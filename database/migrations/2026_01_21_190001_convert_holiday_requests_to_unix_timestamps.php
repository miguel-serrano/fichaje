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
     * Convierte los campos de fecha de holiday_requests de DATE/TIMESTAMP a BIGINT (Unix timestamp).
     * Los campos DATE se convierten a timestamp de medianoche (00:00:00).
     */
    public function up(): void
    {
        // Paso 1: Eliminar índice existente que depende de las columnas de fecha
        Schema::table('holiday_requests', function (Blueprint $table) {
            $table->dropIndex('idx_dates');
        });

        // Paso 2: Agregar columnas temporales con tipo BIGINT
        Schema::table('holiday_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('start_date_unix')->nullable()->after('user_id');
            $table->unsignedBigInteger('end_date_unix')->nullable()->after('start_date_unix');
            $table->unsignedBigInteger('created_at_unix')->nullable()->after('status');
            $table->unsignedBigInteger('updated_at_unix')->nullable()->after('created_at_unix');
        });

        // Paso 3: Migrar datos existentes
        // Para DATE, convertimos a timestamp de medianoche usando UNIX_TIMESTAMP con CONCAT
        DB::statement("UPDATE holiday_requests SET start_date_unix = UNIX_TIMESTAMP(CONCAT(start_date, ' 00:00:00'))");
        DB::statement("UPDATE holiday_requests SET end_date_unix = UNIX_TIMESTAMP(CONCAT(end_date, ' 00:00:00'))");
        DB::statement('UPDATE holiday_requests SET created_at_unix = UNIX_TIMESTAMP(created_at)');
        DB::statement('UPDATE holiday_requests SET updated_at_unix = UNIX_TIMESTAMP(updated_at)');

        // Paso 4: Eliminar columnas antiguas
        Schema::table('holiday_requests', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'created_at', 'updated_at']);
        });

        // Paso 5: Renombrar columnas nuevas
        Schema::table('holiday_requests', function (Blueprint $table) {
            $table->renameColumn('start_date_unix', 'start_date');
            $table->renameColumn('end_date_unix', 'end_date');
            $table->renameColumn('created_at_unix', 'created_at');
            $table->renameColumn('updated_at_unix', 'updated_at');
        });

        // Paso 6: Hacer campos NOT NULL donde corresponda
        Schema::table('holiday_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('start_date')->nullable(false)->change();
            $table->unsignedBigInteger('end_date')->nullable(false)->change();
            $table->unsignedBigInteger('created_at')->nullable(false)->change();
            $table->unsignedBigInteger('updated_at')->nullable(false)->change();
        });

        // Paso 7: Recrear índice
        Schema::table('holiday_requests', function (Blueprint $table) {
            $table->index(['start_date', 'end_date'], 'idx_dates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Convierte los campos de holiday_requests de BIGINT a DATE/TIMESTAMP.
     */
    public function down(): void
    {
        // Paso 1: Eliminar índice
        Schema::table('holiday_requests', function (Blueprint $table) {
            $table->dropIndex('idx_dates');
        });

        // Paso 2: Agregar columnas temporales con tipos originales
        Schema::table('holiday_requests', function (Blueprint $table) {
            $table->date('start_date_dt')->nullable()->after('user_id');
            $table->date('end_date_dt')->nullable()->after('start_date_dt');
            $table->timestamp('created_at_ts')->nullable()->after('status');
            $table->timestamp('updated_at_ts')->nullable()->after('created_at_ts');
        });

        // Paso 3: Migrar datos de vuelta
        // Para DATE, extraemos solo la parte de fecha
        DB::statement('UPDATE holiday_requests SET start_date_dt = DATE(FROM_UNIXTIME(start_date))');
        DB::statement('UPDATE holiday_requests SET end_date_dt = DATE(FROM_UNIXTIME(end_date))');
        DB::statement('UPDATE holiday_requests SET created_at_ts = FROM_UNIXTIME(created_at)');
        DB::statement('UPDATE holiday_requests SET updated_at_ts = FROM_UNIXTIME(updated_at)');

        // Paso 4: Eliminar columnas BIGINT
        Schema::table('holiday_requests', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'created_at', 'updated_at']);
        });

        // Paso 5: Renombrar columnas
        Schema::table('holiday_requests', function (Blueprint $table) {
            $table->renameColumn('start_date_dt', 'start_date');
            $table->renameColumn('end_date_dt', 'end_date');
            $table->renameColumn('created_at_ts', 'created_at');
            $table->renameColumn('updated_at_ts', 'updated_at');
        });

        // Paso 6: Hacer campos NOT NULL
        Schema::table('holiday_requests', function (Blueprint $table) {
            $table->date('start_date')->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
        });

        // Paso 7: Recrear índice
        Schema::table('holiday_requests', function (Blueprint $table) {
            $table->index(['start_date', 'end_date'], 'idx_dates');
        });
    }
};
