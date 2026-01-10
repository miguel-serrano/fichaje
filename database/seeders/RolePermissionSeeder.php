<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = DB::table('roles')->pluck('id', 'slug');
        $permissions = DB::table('permissions')->pluck('id', 'slug');

        $rolePermissions = [];
        $now = now();

        // Super Admin - todos los permisos
        foreach ($permissions as $permissionId) {
            $rolePermissions[] = [
                'role_id' => $roles['super_admin'],
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Admin
        $adminPermissions = [
            'user.view', 'user.create', 'user.update', 'user.delete', 'user.toggle_active',
            'timetracking.view_all', 'timetracking.edit_any', 'timetracking.delete_any', 'timetracking.reports',
            'holiday.view_pending', 'holiday.view_approved', 'holiday.approve', 'holiday.reject',
            'authorization.assign_roles',
        ];
        foreach ($adminPermissions as $slug) {
            if (isset($permissions[$slug])) {
                $rolePermissions[] = [
                    'role_id' => $roles['admin'],
                    'permission_id' => $permissions[$slug],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Supervisor
        $supervisorPermissions = [
            'user.view', 'user.view_own', 'user.update_own',
            'timetracking.view_own', 'timetracking.clockin', 'timetracking.clockout',
            'timetracking.view_all', 'timetracking.reports',
            'holiday.request', 'holiday.view_own', 'holiday.view_pending', 'holiday.view_approved', 'holiday.approve', 'holiday.reject',
        ];
        foreach ($supervisorPermissions as $slug) {
            if (isset($permissions[$slug])) {
                $rolePermissions[] = [
                    'role_id' => $roles['supervisor'],
                    'permission_id' => $permissions[$slug],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Employee
        $employeePermissions = [
            'user.view_own', 'user.update_own',
            'timetracking.view_own', 'timetracking.clockin', 'timetracking.clockout',
            'holiday.request', 'holiday.view_own',
        ];
        foreach ($employeePermissions as $slug) {
            if (isset($permissions[$slug])) {
                $rolePermissions[] = [
                    'role_id' => $roles['employee'],
                    'permission_id' => $permissions[$slug],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('role_permission')->upsert(
            $rolePermissions,
            ['role_id', 'permission_id'],
            ['updated_at']
        );

        $this->command->info('Role-Permission associations seeded: '.count($rolePermissions).' associations');
    }
}
