<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Administrador',
                'slug' => 'super_admin',
                'description' => 'Acceso total al sistema',
                'is_system' => true,
                'hierarchy' => 100,
            ],
            [
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Gestion de usuarios y reportes',
                'is_system' => true,
                'hierarchy' => 80,
            ],
            [
                'name' => 'Supervisor',
                'slug' => 'supervisor',
                'description' => 'Supervision de equipo y aprobacion de vacaciones',
                'is_system' => true,
                'hierarchy' => 50,
            ],
            [
                'name' => 'Empleado',
                'slug' => 'employee',
                'description' => 'Usuario basico con acceso a fichajes',
                'is_system' => true,
                'hierarchy' => 10,
            ],
        ];

        $now = time();
        foreach ($roles as &$role) {
            $role['created_at'] = $now;
            $role['updated_at'] = $now;
        }

        DB::table('roles')->upsert(
            $roles,
            ['slug'],
            ['name', 'description', 'is_system', 'hierarchy', 'updated_at']
        );

        $this->command->info('Roles seeded: '.count($roles).' roles');
    }
}
