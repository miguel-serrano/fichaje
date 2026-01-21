<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Convierte los campos de fecha de las tablas de autorización a BIGINT (Unix timestamp).
     * Tablas afectadas: roles, permissions, role_permission, user_role
     */
    public function up(): void
    {
        $this->convertTable('roles');
        $this->convertTable('permissions');
        $this->convertTable('role_permission');
        $this->convertTable('user_role');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->revertTable('roles');
        $this->revertTable('permissions');
        $this->revertTable('role_permission');
        $this->revertTable('user_role');
    }

    private function convertTable(string $tableName): void
    {
        // Paso 1: Agregar columnas temporales
        Schema::table($tableName, function (Blueprint $table) {
            $table->unsignedBigInteger('created_at_unix')->nullable();
            $table->unsignedBigInteger('updated_at_unix')->nullable();
        });

        // Paso 2: Migrar datos
        DB::statement("UPDATE {$tableName} SET created_at_unix = UNIX_TIMESTAMP(created_at) WHERE created_at IS NOT NULL");
        DB::statement("UPDATE {$tableName} SET updated_at_unix = UNIX_TIMESTAMP(updated_at) WHERE updated_at IS NOT NULL");

        // Paso 3: Eliminar columnas antiguas
        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });

        // Paso 4: Renombrar columnas
        Schema::table($tableName, function (Blueprint $table) {
            $table->renameColumn('created_at_unix', 'created_at');
            $table->renameColumn('updated_at_unix', 'updated_at');
        });
    }

    private function revertTable(string $tableName): void
    {
        // Paso 1: Agregar columnas temporales
        Schema::table($tableName, function (Blueprint $table) {
            $table->timestamp('created_at_ts')->nullable();
            $table->timestamp('updated_at_ts')->nullable();
        });

        // Paso 2: Migrar datos de vuelta
        DB::statement("UPDATE {$tableName} SET created_at_ts = FROM_UNIXTIME(created_at) WHERE created_at IS NOT NULL");
        DB::statement("UPDATE {$tableName} SET updated_at_ts = FROM_UNIXTIME(updated_at) WHERE updated_at IS NOT NULL");

        // Paso 3: Eliminar columnas BIGINT
        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });

        // Paso 4: Renombrar columnas
        Schema::table($tableName, function (Blueprint $table) {
            $table->renameColumn('created_at_ts', 'created_at');
            $table->renameColumn('updated_at_ts', 'updated_at');
        });
    }
};
