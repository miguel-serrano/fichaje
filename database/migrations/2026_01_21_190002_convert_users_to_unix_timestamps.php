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
     * Convierte los campos created_at y updated_at de users de TIMESTAMP a BIGINT (Unix timestamp).
     */
    public function up(): void
    {
        // Paso 1: Agregar columnas temporales con tipo BIGINT
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('created_at_unix')->nullable()->after('is_active');
            $table->unsignedBigInteger('updated_at_unix')->nullable()->after('created_at_unix');
        });

        // Paso 2: Migrar datos existentes
        DB::statement('UPDATE users SET created_at_unix = UNIX_TIMESTAMP(created_at) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE users SET updated_at_unix = UNIX_TIMESTAMP(updated_at) WHERE updated_at IS NOT NULL');

        // Paso 3: Eliminar columnas antiguas
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });

        // Paso 4: Renombrar columnas nuevas
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('created_at_unix', 'created_at');
            $table->renameColumn('updated_at_unix', 'updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Paso 1: Agregar columnas temporales con tipo TIMESTAMP
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('created_at_ts')->nullable();
            $table->timestamp('updated_at_ts')->nullable();
        });

        // Paso 2: Migrar datos de vuelta
        DB::statement('UPDATE users SET created_at_ts = FROM_UNIXTIME(created_at) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE users SET updated_at_ts = FROM_UNIXTIME(updated_at) WHERE updated_at IS NOT NULL');

        // Paso 3: Eliminar columnas BIGINT
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });

        // Paso 4: Renombrar columnas
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('created_at_ts', 'created_at');
            $table->renameColumn('updated_at_ts', 'updated_at');
        });
    }
};
