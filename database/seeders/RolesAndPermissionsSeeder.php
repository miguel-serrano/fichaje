<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create system roles
        $superAdmin = Role::firstOrCreate(
            ['slug' => 'super_admin'],
            [
                'name' => 'Super Administrador',
                'description' => 'Acceso total al sistema',
                'is_system' => true,
                'hierarchy' => 100,
            ]
        );

        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrador',
                'description' => 'Administrador del sistema',
                'is_system' => true,
                'hierarchy' => 90,
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'employee'],
            [
                'name' => 'Empleado',
                'description' => 'Usuario empleado estándar',
                'is_system' => true,
                'hierarchy' => 10,
            ]
        );

        // Create authorization permissions
        $authPermissions = [
            ['slug' => 'authorization.manage_roles', 'name' => 'Gestionar Roles', 'description' => 'Crear, editar y eliminar roles'],
            ['slug' => 'authorization.manage_permissions', 'name' => 'Gestionar Permisos', 'description' => 'Crear, editar y eliminar permisos'],
            ['slug' => 'authorization.assign_roles', 'name' => 'Asignar Roles', 'description' => 'Asignar y quitar roles a usuarios'],
        ];

        foreach ($authPermissions as $permData) {
            $permission = Permission::firstOrCreate(
                ['slug' => $permData['slug']],
                [
                    'name' => $permData['name'],
                    'description' => $permData['description'],
                    'bounded_context' => 'Authorization',
                    'is_system' => true,
                ]
            );

            // Assign authorization permissions to admin role
            if (! $admin->permissions()->where('permissions.id', $permission->id)->exists()) {
                $admin->permissions()->attach($permission->id);
            }
        }

        // Assign super_admin role to users with is_admin = true
        $adminUsers = User::where('is_admin', true)->get();
        foreach ($adminUsers as $user) {
            if (! $user->roles()->where('roles.id', $superAdmin->id)->exists()) {
                $user->roles()->attach($superAdmin->id);
            }
        }

        $this->command->info('Roles y permisos de autorización creados correctamente.');
        $this->command->info("Rol super_admin asignado a {$adminUsers->count()} usuario(s) admin.");
    }
}
