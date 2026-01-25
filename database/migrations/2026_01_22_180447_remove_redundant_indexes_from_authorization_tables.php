<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina índices redundantes de las tablas de Authorization.
 *
 * Índices eliminados:
 * - roles.slug: redundante con UNIQUE constraint
 * - permissions.slug: redundante con UNIQUE constraint
 * - role_permission.role_id: redundante con UNIQUE(role_id, permission_id)
 * - user_role.user_id: redundante con UNIQUE(user_id, role_id)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // roles: eliminar índice redundante en slug (UNIQUE ya lo cubre)
        if ($this->indexExists('roles', 'roles_slug_index')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropIndex('roles_slug_index');
            });
        }

        // permissions: eliminar índice redundante en slug (UNIQUE ya lo cubre)
        if ($this->indexExists('permissions', 'permissions_slug_index')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropIndex('permissions_slug_index');
            });
        }

        // role_permission: eliminar índice en role_id (UNIQUE compuesto ya lo cubre como prefijo)
        if ($this->indexExists('role_permission', 'role_permission_role_id_index')) {
            Schema::table('role_permission', function (Blueprint $table) {
                $table->dropIndex('role_permission_role_id_index');
            });
        }

        // user_role: eliminar índice en user_id (UNIQUE compuesto ya lo cubre como prefijo)
        if ($this->indexExists('user_role', 'user_role_user_id_index')) {
            Schema::table('user_role', function (Blueprint $table) {
                $table->dropIndex('user_role_user_id_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recrear índices si se hace rollback
        Schema::table('roles', function (Blueprint $table) {
            $table->index('slug');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->index('slug');
        });

        Schema::table('role_permission', function (Blueprint $table) {
            $table->index('role_id');
        });

        Schema::table('user_role', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    /**
     * Verifica si un índice existe en una tabla.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select('SHOW INDEXES FROM '.$table.' WHERE Key_name = ?', [$indexName]);

        return ! empty($indexes);
    }
};
