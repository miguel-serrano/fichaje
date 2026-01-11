<?php

namespace Tests\Feature\Authorization;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_super_admin_has_all_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->assertTrue($user->hasRole('super_admin'));
        $this->assertTrue($user->hasPermission('user.delete'));
        $this->assertTrue($user->hasPermission('authorization.manage_roles'));
        $this->assertTrue($user->hasPermission('holiday.approve'));
    }

    public function test_employee_has_limited_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');

        $this->assertTrue($user->hasRole('employee'));
        // Employee puede fichar y pedir vacaciones
        $this->assertTrue($user->hasPermission('timetracking.clockin'));
        $this->assertTrue($user->hasPermission('timetracking.clockout'));
        $this->assertTrue($user->hasPermission('holiday.request'));
        $this->assertTrue($user->hasPermission('user.view_own'));
        $this->assertTrue($user->hasPermission('timetracking.view_own'));
        // Pero no tiene permisos de administración
        $this->assertFalse($user->hasPermission('user.delete'));
        $this->assertFalse($user->hasPermission('authorization.manage_roles'));
    }

    public function test_user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');
        $user->assignRole('supervisor');

        $this->assertTrue($user->hasRole('employee'));
        $this->assertTrue($user->hasRole('supervisor'));
        $this->assertTrue($user->hasPermission('holiday.approve'));
        $this->assertTrue($user->hasPermission('timetracking.clockin'));
    }

    public function test_role_can_be_removed_from_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');
        $user->assignRole('supervisor');

        $this->assertTrue($user->hasRole('supervisor'));

        $user->removeRole('supervisor');

        $this->assertFalse($user->hasRole('supervisor'));
        $this->assertTrue($user->hasRole('employee'));
    }

    public function test_admin_role_has_user_management_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($user->hasPermission('user.view'));
        $this->assertTrue($user->hasPermission('user.create'));
        $this->assertTrue($user->hasPermission('user.update'));
        $this->assertTrue($user->hasPermission('user.delete'));
        $this->assertTrue($user->hasPermission('user.toggle_active'));
        $this->assertTrue($user->hasPermission('authorization.assign_roles'));
    }

    public function test_supervisor_can_approve_holidays(): void
    {
        $user = User::factory()->create();
        $user->assignRole('supervisor');

        $this->assertTrue($user->hasPermission('holiday.approve'));
        $this->assertTrue($user->hasPermission('holiday.reject'));
        $this->assertTrue($user->hasPermission('holiday.view_pending'));
    }

    public function test_roles_have_correct_hierarchy(): void
    {
        $superAdmin = Role::where('slug', 'super_admin')->first();
        $admin = Role::where('slug', 'admin')->first();
        $supervisor = Role::where('slug', 'supervisor')->first();
        $employee = Role::where('slug', 'employee')->first();

        $this->assertEquals(100, $superAdmin->hierarchy);
        $this->assertEquals(80, $admin->hierarchy);
        $this->assertEquals(50, $supervisor->hierarchy);
        $this->assertEquals(10, $employee->hierarchy);
    }

    public function test_permissions_are_grouped_by_bounded_context(): void
    {
        $userPermissions = Permission::where('bounded_context', 'User')->count();
        $timetrackingPermissions = Permission::where('bounded_context', 'TimeTracking')->count();
        $holidayPermissions = Permission::where('bounded_context', 'Holiday')->count();
        $authorizationPermissions = Permission::where('bounded_context', 'Authorization')->count();

        $this->assertEquals(7, $userPermissions);
        $this->assertEquals(8, $timetrackingPermissions);
        $this->assertEquals(6, $holidayPermissions);
        $this->assertEquals(3, $authorizationPermissions);
    }

    public function test_is_admin_middleware_works_with_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user);

        $response = $this->get('/users');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_employee_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');

        $this->actingAs($user);

        $response = $this->get('/users');

        $this->assertEquals(403, $response->getStatusCode());
    }
}
