<?php

declare(strict_types=1);

namespace Tests\Feature\Holiday;

use App\Models\HolidayRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateHolidayRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanViewHolidaysPage(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('holidays.index'));

        $response->assertStatus(200);
        $response->assertViewIs('holidays.index');
    }

    public function testUserCanCreateValidHolidayRequest(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

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

    public function testUserCannotCreateHolidayWithEndDateBeforeStartDate(): void
    {
        $user = User::factory()->create();

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

    public function testUserCannotCreateHolidayWithStartDateInPast(): void
    {
        $user = User::factory()->create();

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

    public function testUserCannotCreateOverlappingHolidayRequest(): void
    {
        $user = User::factory()->create();
        User::factory()->admin()->create();

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

        $this->assertDatabaseCount('holiday_requests', 1);
    }

    public function testUserCanSeePreviousHolidayRequests(): void
    {
        $user = User::factory()->create();

        HolidayRequest::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('holidays.index'));

        $response->assertStatus(200);
        $response->assertViewHas('holidays', function ($holidays) {
            return 3 === count($holidays);
        });
    }

    public function testAdminReceivesNotificationWhenHolidayRequested(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

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

    public function testGuestCannotAccessHolidaysPage(): void
    {
        $response = $this->get(route('holidays.index'));

        $response->assertRedirect(route('login'));
    }
}
