<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User context
            ['name' => 'Ver usuarios', 'slug' => 'user.view', 'bounded_context' => 'User', 'description' => 'Ver lista de usuarios', 'is_system' => true],
            ['name' => 'Ver perfil propio', 'slug' => 'user.view_own', 'bounded_context' => 'User', 'description' => 'Ver su propio perfil', 'is_system' => true],
            ['name' => 'Crear usuarios', 'slug' => 'user.create', 'bounded_context' => 'User', 'description' => 'Crear nuevos usuarios', 'is_system' => true],
            ['name' => 'Actualizar usuarios', 'slug' => 'user.update', 'bounded_context' => 'User', 'description' => 'Modificar datos de usuarios', 'is_system' => true],
            ['name' => 'Actualizar perfil propio', 'slug' => 'user.update_own', 'bounded_context' => 'User', 'description' => 'Modificar su propio perfil', 'is_system' => true],
            ['name' => 'Eliminar usuarios', 'slug' => 'user.delete', 'bounded_context' => 'User', 'description' => 'Eliminar usuarios del sistema', 'is_system' => true],
            ['name' => 'Activar/Desactivar usuarios', 'slug' => 'user.toggle_active', 'bounded_context' => 'User', 'description' => 'Cambiar estado activo de usuarios', 'is_system' => true],

            // TimeTracking context
            ['name' => 'Ver fichajes propios', 'slug' => 'timetracking.view_own', 'bounded_context' => 'TimeTracking', 'description' => 'Ver sus propios fichajes', 'is_system' => true],
            ['name' => 'Ver todos los fichajes', 'slug' => 'timetracking.view_all', 'bounded_context' => 'TimeTracking', 'description' => 'Ver fichajes de todos los usuarios', 'is_system' => true],
            ['name' => 'Fichar entrada', 'slug' => 'timetracking.clockin', 'bounded_context' => 'TimeTracking', 'description' => 'Realizar fichaje de entrada', 'is_system' => true],
            ['name' => 'Fichar salida', 'slug' => 'timetracking.clockout', 'bounded_context' => 'TimeTracking', 'description' => 'Realizar fichaje de salida', 'is_system' => true],
            ['name' => 'Editar fichajes propios', 'slug' => 'timetracking.edit_own', 'bounded_context' => 'TimeTracking', 'description' => 'Modificar sus propios fichajes', 'is_system' => true],
            ['name' => 'Editar cualquier fichaje', 'slug' => 'timetracking.edit_any', 'bounded_context' => 'TimeTracking', 'description' => 'Modificar fichajes de cualquier usuario', 'is_system' => true],
            ['name' => 'Eliminar fichajes', 'slug' => 'timetracking.delete_any', 'bounded_context' => 'TimeTracking', 'description' => 'Eliminar fichajes de cualquier usuario', 'is_system' => true],
            ['name' => 'Ver reportes', 'slug' => 'timetracking.reports', 'bounded_context' => 'TimeTracking', 'description' => 'Acceder a reportes de tiempo', 'is_system' => true],

            // Holiday context
            ['name' => 'Solicitar vacaciones', 'slug' => 'holiday.request', 'bounded_context' => 'Holiday', 'description' => 'Crear solicitudes de vacaciones', 'is_system' => true],
            ['name' => 'Ver vacaciones propias', 'slug' => 'holiday.view_own', 'bounded_context' => 'Holiday', 'description' => 'Ver sus propias solicitudes', 'is_system' => true],
            ['name' => 'Ver solicitudes pendientes', 'slug' => 'holiday.view_pending', 'bounded_context' => 'Holiday', 'description' => 'Ver solicitudes pendientes de todos', 'is_system' => true],
            ['name' => 'Ver vacaciones aprobadas', 'slug' => 'holiday.view_approved', 'bounded_context' => 'Holiday', 'description' => 'Ver vacaciones aprobadas de todos', 'is_system' => true],
            ['name' => 'Aprobar vacaciones', 'slug' => 'holiday.approve', 'bounded_context' => 'Holiday', 'description' => 'Aprobar solicitudes de vacaciones', 'is_system' => true],
            ['name' => 'Rechazar vacaciones', 'slug' => 'holiday.reject', 'bounded_context' => 'Holiday', 'description' => 'Rechazar solicitudes de vacaciones', 'is_system' => true],

            // Authorization context
            ['name' => 'Gestionar roles', 'slug' => 'authorization.manage_roles', 'bounded_context' => 'Authorization', 'description' => 'Crear, editar, eliminar roles', 'is_system' => true],
            ['name' => 'Gestionar permisos', 'slug' => 'authorization.manage_permissions', 'bounded_context' => 'Authorization', 'description' => 'Asignar permisos a roles', 'is_system' => true],
            ['name' => 'Asignar roles', 'slug' => 'authorization.assign_roles', 'bounded_context' => 'Authorization', 'description' => 'Asignar roles a usuarios', 'is_system' => true],
        ];

        $now = now();
        foreach ($permissions as &$permission) {
            $permission['created_at'] = $now;
            $permission['updated_at'] = $now;
        }

        DB::table('permissions')->upsert(
            $permissions,
            ['slug'],
            ['name', 'bounded_context', 'description', 'is_system', 'updated_at']
        );

        $this->command->info('Permissions seeded: '.count($permissions).' permissions');
    }
}
