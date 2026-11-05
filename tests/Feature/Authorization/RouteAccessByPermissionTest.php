<?php

namespace Tests\Feature\Authorization;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\HolidayRequest;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RouteAccessByPermissionTest extends TestCase
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

    public function testAdminCanListUsers(): void
    {
        $response = $this->actingAs($this->createUserWithRole('admin'))->get(route('users.index'));

        $response->assertOk();
        $response->assertViewIs('users.index');
    }

    public function testAdminCanViewAUser(): void
    {
        $employee = $this->createUserWithRole('employee');

        $response = $this->actingAs($this->createUserWithRole('admin'))->get(route('user.show', $employee->id));

        $response->assertOk();
    }

    public function testEmployeeCannotListUsers(): void
    {
        $this->actingAs($this->createUserWithRole('employee'))->get(route('users.index'))->assertForbidden();
    }

    public function testSupervisorCanAccessHolidayReview(): void
    {
        $response = $this->actingAs($this->createUserWithRole('supervisor'))->get(route('admin.holidays.index'));

        $response->assertOk();
        $response->assertViewIs('admin.holidays.index');
    }

    public function testSupervisorCanApproveAnEmployeeRequest(): void
    {
        $employee = $this->createUserWithRole('employee');
        $holidayRequest = HolidayRequest::factory()->create(['user_id' => $employee->id]);

        $response = $this->actingAs($this->createUserWithRole('supervisor'))
            ->post(route('admin.holidays.approve', $holidayRequest->id));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('holiday_requests', ['id' => $holidayRequest->id, 'status' => 'approved']);
    }

    public function testEmployeeCannotAccessHolidayReview(): void
    {
        $this->actingAs($this->createUserWithRole('employee'))->get(route('admin.holidays.index'))->assertForbidden();
    }

    public function testAdminWithoutManageRolesCannotAccessRoleManagement(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)->get(route('admin.roles.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.permissions.index'))->assertForbidden();
    }

    public function testSuperAdminCanAccessRoleAndPermissionManagement(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');

        $this->actingAs($superAdmin)->get(route('admin.roles.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.permissions.index'))->assertOk();
    }

    public function testEmployeeCannotDeleteUsers(): void
    {
        $target = $this->createUserWithRole('employee');

        $this->actingAs($this->createUserWithRole('employee'))->delete(route('user.delete', $target->id))->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function testEmployeeMenuShowsTrackingButNoAdminSections(): void
    {
        $response = $this->actingAs($this->createUserWithRole('employee'))->get(route('registro_horario.index'));

        $response->assertOk();
        $response->assertSee('Seguimiento');
        $response->assertDontSee('Gestionar Vacaciones');
        $response->assertDontSee(route('users.index'));
        $response->assertDontSee(route('admin.roles.index'));
    }

    public function testSupervisorMenuShowsHolidayReview(): void
    {
        $response = $this->actingAs($this->createUserWithRole('supervisor'))->get(route('registro_horario.index'));

        $response->assertSee('Gestionar Vacaciones');
        $response->assertDontSee(route('admin.roles.index'));
    }

    public function testAdminMenuShowsUsersButNotRoleManagement(): void
    {
        $response = $this->actingAs($this->createUserWithRole('admin'))->get(route('registro_horario.index'));

        $response->assertSee(route('users.index'));
        $response->assertDontSee(route('admin.roles.index'));
        $response->assertDontSee(route('admin.permissions.index'));
    }

    public function testSuperAdminMenuShowsEverySection(): void
    {
        $response = $this->actingAs($this->createUserWithRole('super_admin'))->get(route('registro_horario.index'));

        $response->assertSee(route('users.index'));
        $response->assertSee(route('admin.roles.index'));
        $response->assertSee(route('admin.permissions.index'));
        $response->assertSee('Gestionar Vacaciones');
    }

    private function createUserWithRole(string $roleSlug): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($roleSlug);

        return $user;
    }
}
