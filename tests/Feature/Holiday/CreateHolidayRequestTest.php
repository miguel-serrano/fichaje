<?php

declare(strict_types=1);

namespace Tests\Feature\Holiday;

use App\Models\HolidayRequest;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class CreateHolidayRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Ejecutar seeders de roles y permisos
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);
    }

    private function assignEmployeeRole(User $user): void
    {
        $employeeRole = Role::where('slug', 'employee')->first();
        if ($employeeRole) {
            $user->roles()->syncWithoutDetaching([$employeeRole->id]);
        }
    }

    private function assignSuperAdminRole(User $user): void
    {
        $superAdminRole = Role::where('slug', 'super_admin')->first();
        if ($superAdminRole) {
            $user->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }
    }

    public function test_user_can_view_holidays_page(): void
    {
        $user = User::factory()->create();
        $this->assignEmployeeRole($user);

        $response = $this->actingAs($user)->get(route('holidays.index'));

        $response->assertStatus(200);
        $response->assertViewIs('holidays.index');
    }

    public function test_user_can_create_valid_holiday_request(): void
    {
        $user = User::factory()->create();
        $this->assignEmployeeRole($user);
        $admin = User::factory()->admin()->create();
        $this->assignSuperAdminRole($admin);

        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(5)->format('Y-m-d');

        $response = $this->actingAs($user)->post(route('holidays.store'), [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $response->assertRedirect(route('holidays.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('holiday_requests', [
            'user_id' => $user->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_create_holiday_with_end_date_before_start_date(): void
    {
        $user = User::factory()->create();
        $this->assignEmployeeRole($user);

        $startDate = now()->addDays(5)->format('Y-m-d');
        $endDate = now()->addDays(1)->format('Y-m-d');

        $response = $this->actingAs($user)->post(route('holidays.store'), [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $response->assertSessionHasErrors('end_date');

        $this->assertDatabaseMissing('holiday_requests', [
            'user_id' => $user->id,
        ]);
    }

    public function test_user_cannot_create_holiday_with_start_date_in_past(): void
    {
        $user = User::factory()->create();
        $this->assignEmployeeRole($user);

        $startDate = now()->subDays(1)->format('Y-m-d');
        $endDate = now()->addDays(5)->format('Y-m-d');

        $response = $this->actingAs($user)->post(route('holidays.store'), [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $response->assertSessionHasErrors('start_date');

        $this->assertDatabaseMissing('holiday_requests', [
            'user_id' => $user->id,
        ]);
    }

    public function test_user_cannot_create_overlapping_holiday_request(): void
    {
        $user = User::factory()->create();
        $this->assignEmployeeRole($user);
        $admin = User::factory()->admin()->create();
        $this->assignSuperAdminRole($admin);

        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(10)->format('Y-m-d');

        HolidayRequest::factory()->create([
            'user_id' => $user->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'pending',
        ]);

        $overlappingStartDate = now()->addDays(5)->format('Y-m-d');
        $overlappingEndDate = now()->addDays(15)->format('Y-m-d');

        $response = $this->actingAs($user)->post(route('holidays.store'), [
            'start_date' => $overlappingStartDate,
            'end_date' => $overlappingEndDate,
        ]);

        $response->assertRedirect(route('holidays.index'));
        $response->assertSessionHas('error');

        // Solo debe haber 1 solicitud para este usuario (la original, no la solapada)
        $this->assertEquals(1, HolidayRequest::where('user_id', $user->id)->count());
    }

    public function test_user_can_see_previous_holiday_requests(): void
    {
        $user = User::factory()->create();
        $this->assignEmployeeRole($user);

        HolidayRequest::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('holidays.index'));

        $response->assertStatus(200);
        $response->assertViewHas('holidays', function ($holidays) {
            return count($holidays) === 3;
        });
    }

    public function test_admin_receives_notification_when_holiday_requested(): void
    {
        $user = User::factory()->create();
        $this->assignEmployeeRole($user);
        $admin = User::factory()->admin()->create();
        $this->assignSuperAdminRole($admin);

        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(5)->format('Y-m-d');

        $this->actingAs($user)->post(route('holidays.store'), [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'type' => 'holiday_requested',
        ]);
    }

    public function test_guest_cannot_access_holidays_page(): void
    {
        $response = $this->get(route('holidays.index'));

        $response->assertRedirect(route('login'));
    }
}
