<?php

namespace Tests\Feature\TimeTracking;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CloseOrphanTimeEntriesCommandTest extends TestCase
{
    private User $user;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $now = time();
        $this->user = User::create([
            'uuid' => Str::orderedUuid(),
            'name' => 'Test User',
            'email' => 'testuser@test.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->adminUser = User::create([
            'uuid' => Str::orderedUuid(),
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_command_closes_orphan_time_entries_from_previous_days(): void
    {
        // Create an orphan entry from yesterday at 10:00
        $yesterday = strtotime('yesterday 10:00:00');

        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday,
            'salida' => null,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ]);

        $this->artisan('time-entries:close-orphans')
            ->expectsOutput('Buscando fichajes huérfanos...')
            ->expectsOutput('Cerrados 1 fichajes huérfanos de 1 usuarios.')
            ->assertSuccessful();

        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->user->id,
            'auto_closed' => true,
            'auto_close_reason' => 'max_hours_exceeded',
        ]);
    }

    public function test_closes_at_8_hours_when_entry_started_before_16h(): void
    {
        // Entry at 10:00 -> should close at 18:00 (8 hours later)
        $yesterday = strtotime('yesterday 10:00:00');

        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday,
            'salida' => null,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ]);

        $this->artisan('time-entries:close-orphans')->assertSuccessful();

        $entry = DB::table('time_entries')->where('user_id', $this->user->id)->first();

        $expectedSalida = $yesterday + (8 * 3600);
        $this->assertEquals($expectedSalida, $entry->salida);
        $this->assertEquals('max_hours_exceeded', $entry->auto_close_reason);
        $this->assertTrue((bool) $entry->auto_closed);
    }

    public function test_closes_at_end_of_day_when_entry_started_after_16h(): void
    {
        // Entry at 17:00 -> should close at 23:59:59 (end of day, before 8 hours)
        $yesterday = strtotime('yesterday 17:00:00');
        $yesterdayDate = date('Y-m-d', $yesterday);

        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday,
            'salida' => null,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ]);

        $this->artisan('time-entries:close-orphans')->assertSuccessful();

        $entry = DB::table('time_entries')->where('user_id', $this->user->id)->first();

        $expectedSalida = strtotime($yesterdayDate.' 23:59:59');
        $this->assertEquals($expectedSalida, $entry->salida);
        $this->assertEquals('end_of_day', $entry->auto_close_reason);
        $this->assertTrue((bool) $entry->auto_closed);
    }

    public function test_does_not_close_entries_from_today(): void
    {
        // Entry from today should NOT be closed
        $today = strtotime('today 09:00:00');

        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $today,
            'salida' => null,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $today,
            'updated_at' => $today,
        ]);

        $this->artisan('time-entries:close-orphans')
            ->expectsOutput('Buscando fichajes huérfanos...')
            ->expectsOutput('No se encontraron fichajes huérfanos.')
            ->assertSuccessful();

        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->user->id,
            'salida' => null,
            'auto_closed' => false,
        ]);
    }

    public function test_does_not_close_already_closed_entries(): void
    {
        $yesterday = strtotime('yesterday 10:00:00');
        $salidaManual = strtotime('yesterday 18:00:00');

        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday,
            'salida' => $salidaManual,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ]);

        $this->artisan('time-entries:close-orphans')
            ->expectsOutput('No se encontraron fichajes huérfanos.')
            ->assertSuccessful();

        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->user->id,
            'salida' => $salidaManual,
            'auto_closed' => false,
        ]);
    }

    public function test_creates_notification_for_affected_user(): void
    {
        $yesterday = strtotime('yesterday 10:00:00');

        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday,
            'salida' => null,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ]);

        $this->artisan('time-entries:close-orphans')->assertSuccessful();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'time_entry_auto_closed',
        ]);

        $notification = Notification::where('user_id', $this->user->id)->first();
        $this->assertStringContains('Cerrado automáticamente', $notification->message);
    }

    public function test_handles_multiple_users_with_orphan_entries(): void
    {
        $now = time();
        $user2 = User::create([
            'uuid' => Str::orderedUuid(),
            'name' => 'Test User 2',
            'email' => 'testuser2@test.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $yesterday10am = strtotime('yesterday 10:00:00');
        $yesterday17pm = strtotime('yesterday 17:00:00');

        // Entry for user 1
        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday10am,
            'salida' => null,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday10am,
            'updated_at' => $yesterday10am,
        ]);

        // Entry for user 2
        DB::table('time_entries')->insert([
            'user_id' => $user2->id,
            'entrada' => $yesterday17pm,
            'salida' => null,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday10am,
            'updated_at' => $yesterday10am,
        ]);

        $this->artisan('time-entries:close-orphans')
            ->expectsOutput('Cerrados 2 fichajes huérfanos de 2 usuarios.')
            ->assertSuccessful();

        // User 1: closed at 8 hours
        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->user->id,
            'auto_close_reason' => 'max_hours_exceeded',
        ]);

        // User 2: closed at end of day
        $this->assertDatabaseHas('time_entries', [
            'user_id' => $user2->id,
            'auto_close_reason' => 'end_of_day',
        ]);

        // Both users get notifications
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'time_entry_auto_closed',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user2->id,
            'type' => 'time_entry_auto_closed',
        ]);
    }

    public function test_closes_orphan_considering_total_daily_hours(): void
    {
        $yesterday09am = strtotime('yesterday 09:00:00');
        $yesterday13pm = strtotime('yesterday 13:00:00');
        $yesterday14pm = strtotime('yesterday 14:00:00');
        $yesterday18pm = strtotime('yesterday 18:00:00');

        // Closed entry: 09:00 - 13:00 (4 hours)
        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday09am,
            'salida' => $yesterday13pm,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday09am,
            'updated_at' => $yesterday09am,
        ]);

        // Orphan entry: 14:00 - ? (should close at 18:00 to complete 8h total)
        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday14pm,
            'salida' => null,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday14pm,
            'updated_at' => $yesterday14pm,
        ]);

        $this->artisan('time-entries:close-orphans')->assertSuccessful();

        $orphanEntry = DB::table('time_entries')
            ->where('user_id', $this->user->id)
            ->whereNotNull('auto_close_reason')
            ->first();

        // Should close at 18:00 (14:00 + 4h remaining = 18:00)
        $this->assertEquals($yesterday18pm, $orphanEntry->salida);
        $this->assertEquals('max_hours_exceeded', $orphanEntry->auto_close_reason);
    }

    public function test_closes_orphan_at_entrada_when_daily_limit_already_reached(): void
    {
        $yesterday08am = strtotime('yesterday 08:00:00');
        $yesterday16pm = strtotime('yesterday 16:00:00');
        $yesterday17pm = strtotime('yesterday 17:00:00');

        // Closed entry: 08:00 - 16:00 (8 hours - full day)
        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday08am,
            'salida' => $yesterday16pm,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday08am,
            'updated_at' => $yesterday08am,
        ]);

        // Orphan entry: 17:00 - ? (already worked 8h, should close immediately)
        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday17pm,
            'salida' => null,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday17pm,
            'updated_at' => $yesterday17pm,
        ]);

        $this->artisan('time-entries:close-orphans')->assertSuccessful();

        $orphanEntry = DB::table('time_entries')
            ->where('user_id', $this->user->id)
            ->where('auto_closed', true)
            ->first();

        // Should close at entrada time (17:00) since 8h already worked
        $this->assertEquals($yesterday17pm, $orphanEntry->salida);
        $this->assertEquals('max_hours_exceeded', $orphanEntry->auto_close_reason);
    }

    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Failed asserting that '$haystack' contains '$needle'"
        );
    }
}
