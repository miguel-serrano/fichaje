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

class AdminHolidayManagementTest extends TestCase
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

    private function assignSuperAdminRole(User $user): void
    {
        $superAdminRole = Role::where('slug', 'super_admin')->first();
        if ($superAdminRole) {
            $user->roles()->attach($superAdminRole->id);
        }
    }

    private function assignEmployeeRole(User $user): void
    {
        $employeeRole = Role::where('slug', 'employee')->first();
        if ($employeeRole) {
            $user->roles()->attach($employeeRole->id);
        }
    }

    public function test_admin_can_view_pending_holidays(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assignSuperAdminRole($admin);
        $user = User::factory()->create();

        HolidayRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.holidays.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.holidays.index');
    }

    public function test_non_admin_cannot_view_pending_holidays(): void
    {
        $user = User::factory()->create();
        $this->assignEmployeeRole($user);

        $response = $this->actingAs($user)->get(route('admin.holidays.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_approve_holiday_request(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assignSuperAdminRole($admin);
        $user = User::factory()->create();

        $holidayRequest = HolidayRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.holidays.approve', $holidayRequest->id)
        );

        $response->assertRedirect(route('admin.holidays.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('holiday_requests', [
            'id' => $holidayRequest->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_holiday_request(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assignSuperAdminRole($admin);
        $user = User::factory()->create();

        $holidayRequest = HolidayRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.holidays.reject', $holidayRequest->id)
        );

        $response->assertRedirect(route('admin.holidays.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('holiday_requests', [
            'id' => $holidayRequest->id,
            'status' => 'rejected',
        ]);
    }

    public function test_non_admin_cannot_approve_holiday_request(): void
    {
        $user = User::factory()->create();
        $this->assignEmployeeRole($user);
        $otherUser = User::factory()->create();

        $holidayRequest = HolidayRequest::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(
            route('admin.holidays.approve', $holidayRequest->id)
        );

        $response->assertStatus(403);

        $this->assertDatabaseHas('holiday_requests', [
            'id' => $holidayRequest->id,
            'status' => 'pending',
        ]);
    }

    public function test_non_admin_cannot_reject_holiday_request(): void
    {
        $user = User::factory()->create();
        $this->assignEmployeeRole($user);
        $otherUser = User::factory()->create();

        $holidayRequest = HolidayRequest::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(
            route('admin.holidays.reject', $holidayRequest->id)
        );

        $response->assertStatus(403);

        $this->assertDatabaseHas('holiday_requests', [
            'id' => $holidayRequest->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_receives_notification_when_holiday_approved(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assignSuperAdminRole($admin);
        $user = User::factory()->create();

        $holidayRequest = HolidayRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(
            route('admin.holidays.approve', $holidayRequest->id)
        );

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'holiday_approved',
        ]);
    }

    public function test_user_receives_notification_when_holiday_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assignSuperAdminRole($admin);
        $user = User::factory()->create();

        $holidayRequest = HolidayRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(
            route('admin.holidays.reject', $holidayRequest->id)
        );

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'holiday_rejected',
        ]);
    }

    public function test_guest_cannot_access_admin_holidays_page(): void
    {
        $response = $this->get(route('admin.holidays.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_approved_holidays(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assignSuperAdminRole($admin);
        $user = User::factory()->create();

        HolidayRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        HolidayRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.holidays.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.holidays.index');
        $response->assertViewHas('pendingWithUsers');
        $response->assertViewHas('approvedWithUsers');
    }
}
