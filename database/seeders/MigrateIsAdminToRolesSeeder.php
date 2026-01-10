<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateIsAdminToRolesSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRoleId = DB::table('roles')->where('slug', 'super_admin')->value('id');
        $employeeRoleId = DB::table('roles')->where('slug', 'employee')->value('id');

        if (! $superAdminRoleId || ! $employeeRoleId) {
            $this->command->error('Roles not found. Run RoleSeeder first.');

            return;
        }

        $now = now();
        $migratedAdmins = 0;
        $migratedEmployees = 0;

        // Users with is_admin = true -> role super_admin
        $adminUsers = DB::table('users')->where('is_admin', true)->pluck('id');
        foreach ($adminUsers as $userId) {
            $exists = DB::table('user_role')
                ->where('user_id', $userId)
                ->where('role_id', $superAdminRoleId)
                ->exists();

            if (! $exists) {
                DB::table('user_role')->insert([
                    'user_id' => $userId,
                    'role_id' => $superAdminRoleId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $migratedAdmins++;
            }
        }

        // Users with is_admin = false and no role -> role employee
        $regularUsers = DB::table('users')
            ->where('is_admin', false)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('user_role')
                    ->whereRaw('user_role.user_id = users.id');
            })
            ->pluck('id');

        foreach ($regularUsers as $userId) {
            DB::table('user_role')->insert([
                'user_id' => $userId,
                'role_id' => $employeeRoleId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $migratedEmployees++;
        }

        $this->command->info("Migration complete: {$migratedAdmins} admins -> super_admin, {$migratedEmployees} users -> employee");
    }
}
