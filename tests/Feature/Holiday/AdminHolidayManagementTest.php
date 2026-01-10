<?php

declare(strict_types=1);

namespace Tests\Feature\Holiday;

use App\Models\HolidayRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminHolidayManagementTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminCanViewPendingHolidays(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        HolidayRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.holidays.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.holidays.index');
    }

    public function testNonAdminCannotViewPendingHolidays(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.holidays.index'));

        $response->assertStatus(403);
    }

    public function testAdminCanApproveHolidayRequest(): void
    {
        $admin = User::factory()->admin()->create();
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

    public function testAdminCanRejectHolidayRequest(): void
    {
        $admin = User::factory()->admin()->create();
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

    public function testNonAdminCannotApproveHolidayRequest(): void
    {
        $user = User::factory()->create();
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

    public function testNonAdminCannotRejectHolidayRequest(): void
    {
        $user = User::factory()->create();
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

    public function testUserReceivesNotificationWhenHolidayApproved(): void
    {
        $admin = User::factory()->admin()->create();
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

    public function testUserReceivesNotificationWhenHolidayRejected(): void
    {
        $admin = User::factory()->admin()->create();
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

    public function testGuestCannotAccessAdminHolidaysPage(): void
    {
        $response = $this->get(route('admin.holidays.index'));

        $response->assertRedirect(route('login'));
    }
}
