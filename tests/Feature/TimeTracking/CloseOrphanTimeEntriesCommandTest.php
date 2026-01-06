<?php

namespace Tests\Feature\TimeTracking;

use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CloseOrphanTimeEntriesCommandTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'uuid' => Str::orderedUuid(),
            'name' => 'Test User',
            'email' => 'testuser@test.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
    }

    public function test_command_closes_orphan_time_entries_from_previous_days(): void
    {
        // Create an orphan entry from yesterday at 10:00
        $yesterday = Carbon::yesterday()->setHour(10)->setMinute(0)->setSecond(0);

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
        $yesterday = Carbon::yesterday()->setHour(10)->setMinute(0)->setSecond(0);

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

        $expectedSalida = $yesterday->copy()->addHours(8);
        $this->assertEquals($expectedSalida->format('Y-m-d H:i:s'), $entry->salida);
        $this->assertEquals('max_hours_exceeded', $entry->auto_close_reason);
        $this->assertTrue((bool) $entry->auto_closed);
    }

    public function test_closes_at_end_of_day_when_entry_started_after_16h(): void
    {
        // Entry at 17:00 -> should close at 23:59:59 (end of day, before 8 hours)
        $yesterday = Carbon::yesterday()->setHour(17)->setMinute(0)->setSecond(0);

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

        $expectedSalida = $yesterday->copy()->endOfDay();
        $this->assertEquals($expectedSalida->format('Y-m-d H:i:s'), $entry->salida);
        $this->assertEquals('end_of_day', $entry->auto_close_reason);
        $this->assertTrue((bool) $entry->auto_closed);
    }

    public function test_does_not_close_entries_from_today(): void
    {
        // Entry from today should NOT be closed
        $today = Carbon::today()->setHour(9)->setMinute(0)->setSecond(0);

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
        $yesterday = Carbon::yesterday()->setHour(10)->setMinute(0)->setSecond(0);
        $salidaManual = $yesterday->copy()->setHour(18)->setMinute(0)->setSecond(0);

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
        $yesterday = Carbon::yesterday()->setHour(10)->setMinute(0)->setSecond(0);

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
        $user2 = User::create([
            'uuid' => Str::orderedUuid(),
            'name' => 'Test User 2',
            'email' => 'testuser2@test.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $yesterday = Carbon::yesterday()->setHour(10)->setMinute(0)->setSecond(0);

        // Entry for user 1
        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday,
            'salida' => null,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ]);

        // Entry for user 2
        DB::table('time_entries')->insert([
            'user_id' => $user2->id,
            'entrada' => $yesterday->copy()->setHour(17),
            'salida' => null,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
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
        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_closes_orphan_considering_total_daily_hours(): void
    {
        $yesterday = Carbon::yesterday();

        // Closed entry: 09:00 - 13:00 (4 hours)
        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday->copy()->setHour(9)->setMinute(0)->setSecond(0),
            'salida' => $yesterday->copy()->setHour(13)->setMinute(0)->setSecond(0),
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ]);

        // Orphan entry: 14:00 - ? (should close at 18:00 to complete 8h total)
        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday->copy()->setHour(14)->setMinute(0)->setSecond(0),
            'salida' => null,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ]);

        $this->artisan('time-entries:close-orphans')->assertSuccessful();

        $orphanEntry = DB::table('time_entries')
            ->where('user_id', $this->user->id)
            ->whereNotNull('auto_close_reason')
            ->first();

        // Should close at 18:00 (14:00 + 4h remaining = 18:00)
        $expectedSalida = $yesterday->copy()->setHour(18)->setMinute(0)->setSecond(0);
        $this->assertEquals($expectedSalida->format('Y-m-d H:i:s'), $orphanEntry->salida);
        $this->assertEquals('max_hours_exceeded', $orphanEntry->auto_close_reason);
    }

    public function test_closes_orphan_at_entrada_when_daily_limit_already_reached(): void
    {
        $yesterday = Carbon::yesterday();

        // Closed entry: 08:00 - 16:00 (8 hours - full day)
        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $yesterday->copy()->setHour(8)->setMinute(0)->setSecond(0),
            'salida' => $yesterday->copy()->setHour(16)->setMinute(0)->setSecond(0),
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ]);

        // Orphan entry: 17:00 - ? (already worked 8h, should close immediately)
        $orphanEntrada = $yesterday->copy()->setHour(17)->setMinute(0)->setSecond(0);
        DB::table('time_entries')->insert([
            'user_id' => $this->user->id,
            'entrada' => $orphanEntrada,
            'salida' => null,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ]);

        $this->artisan('time-entries:close-orphans')->assertSuccessful();

        $orphanEntry = DB::table('time_entries')
            ->where('user_id', $this->user->id)
            ->where('auto_closed', true)
            ->first();

        // Should close at entrada time (17:00) since 8h already worked
        $this->assertEquals($orphanEntrada->format('Y-m-d H:i:s'), $orphanEntry->salida);
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
