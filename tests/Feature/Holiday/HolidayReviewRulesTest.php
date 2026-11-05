<?php

declare(strict_types=1);

namespace Tests\Feature\Holiday;

use App\Http\Middleware\VerifyCsrfToken;
use App\Mail\DomainEventMail;
use App\Models\HolidayRequest;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class HolidayReviewRulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        Mail::fake();
    }

    public function testApprovingAnApprovedRequestIsRejectedWithoutSideEffects(): void
    {
        $employee = $this->createUserWithRole('employee');
        $holidayRequest = HolidayRequest::factory()->approved()->create(['user_id' => $employee->id]);

        $response = $this->actingAs($this->createUserWithRole('super_admin'))
            ->post(route('admin.holidays.approve', $holidayRequest->id));

        $this->assertReviewRefused($response, 'ya está aprobada');
        $this->assertDatabaseHas('holiday_requests', ['id' => $holidayRequest->id, 'status' => 'approved']);
        $this->assertDatabaseMissing('notifications', ['user_id' => $employee->id]);
        Mail::assertNotQueued(DomainEventMail::class);
    }

    public function testRejectingAnApprovedRequestIsRefused(): void
    {
        $employee = $this->createUserWithRole('employee');
        $holidayRequest = HolidayRequest::factory()->approved()->create(['user_id' => $employee->id]);

        $response = $this->actingAs($this->createUserWithRole('super_admin'))
            ->post(route('admin.holidays.reject', $holidayRequest->id));

        $this->assertReviewRefused($response, 'ya está aprobada');
        $this->assertDatabaseHas('holiday_requests', ['id' => $holidayRequest->id, 'status' => 'approved']);
    }

    public function testApprovingARejectedRequestIsRefused(): void
    {
        $employee = $this->createUserWithRole('employee');
        $holidayRequest = HolidayRequest::factory()->rejected()->create(['user_id' => $employee->id]);

        $response = $this->actingAs($this->createUserWithRole('super_admin'))
            ->post(route('admin.holidays.approve', $holidayRequest->id));

        $this->assertReviewRefused($response, 'ya está rechazada');
        $this->assertDatabaseHas('holiday_requests', ['id' => $holidayRequest->id, 'status' => 'rejected']);
    }

    public function testRejectingARejectedRequestIsRefused(): void
    {
        $employee = $this->createUserWithRole('employee');
        $holidayRequest = HolidayRequest::factory()->rejected()->create(['user_id' => $employee->id]);

        $response = $this->actingAs($this->createUserWithRole('super_admin'))
            ->post(route('admin.holidays.reject', $holidayRequest->id));

        $this->assertReviewRefused($response, 'ya está rechazada');
        $this->assertDatabaseMissing('notifications', ['user_id' => $employee->id]);
    }

    public function testSuperAdminCanApproveOwnRequest(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');
        $holidayRequest = HolidayRequest::factory()->create(['user_id' => $superAdmin->id]);

        $response = $this->actingAs($superAdmin)->post(route('admin.holidays.approve', $holidayRequest->id));

        $response->assertRedirect(route('admin.holidays.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('holiday_requests', ['id' => $holidayRequest->id, 'status' => 'approved']);
    }

    public function testSuperAdminCanRejectOwnRequest(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');
        $holidayRequest = HolidayRequest::factory()->create(['user_id' => $superAdmin->id]);

        $response = $this->actingAs($superAdmin)->post(route('admin.holidays.reject', $holidayRequest->id));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('holiday_requests', ['id' => $holidayRequest->id, 'status' => 'rejected']);
    }

    public function testAdminCannotApproveOwnRequest(): void
    {
        $admin = $this->createUserWithRole('admin');
        $holidayRequest = HolidayRequest::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->post(route('admin.holidays.approve', $holidayRequest->id));

        $response->assertRedirect(route('admin.holidays.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('holiday_requests', ['id' => $holidayRequest->id, 'status' => 'pending']);
    }

    public function testAdminCannotRejectOwnRequest(): void
    {
        $admin = $this->createUserWithRole('admin');
        $holidayRequest = HolidayRequest::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->post(route('admin.holidays.reject', $holidayRequest->id));

        $response->assertRedirect(route('admin.holidays.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('holiday_requests', ['id' => $holidayRequest->id, 'status' => 'pending']);
    }

    public function testAnotherAdminCanApproveAnAdminRequest(): void
    {
        $admin = $this->createUserWithRole('admin');
        $holidayRequest = HolidayRequest::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($this->createUserWithRole('super_admin'))
            ->post(route('admin.holidays.approve', $holidayRequest->id));

        $response->assertRedirect(route('admin.holidays.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('holiday_requests', ['id' => $holidayRequest->id, 'status' => 'approved']);
    }

    public function testApprovingANonexistentRequestShowsAnError(): void
    {
        $response = $this->actingAs($this->createUserWithRole('super_admin'))
            ->post(route('admin.holidays.approve', 999999));

        $response->assertRedirect(route('admin.holidays.index'));
        $response->assertSessionHas('error');
    }

    private function createUserWithRole(string $roleSlug): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($roleSlug);

        return $user;
    }

    private function assertReviewRefused(TestResponse $response, string $expectedMessage): void
    {
        $response->assertRedirect(route('admin.holidays.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString($expectedMessage, session('error'));
    }
}
