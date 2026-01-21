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
     * Convierte los campos de fecha de time_entries de TIMESTAMP a BIGINT (Unix timestamp).
     */
    public function up(): void
    {
        // Paso 1: Eliminar índice existente que depende de las columnas de fecha (si existe)
        $indexExists = DB::select("SHOW INDEXES FROM time_entries WHERE Key_name = 'idx_user_time_range'");
        if (! empty($indexExists)) {
            Schema::table('time_entries', function (Blueprint $table) {
                $table->dropIndex('idx_user_time_range');
            });
        }

        // Paso 2: Agregar columnas temporales con tipo BIGINT
        Schema::table('time_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('entrada_unix')->nullable()->after('user_id');
            $table->unsignedBigInteger('salida_unix')->nullable()->after('entrada_unix');
            $table->unsignedBigInteger('created_at_unix')->nullable()->after('auto_close_reason');
            $table->unsignedBigInteger('updated_at_unix')->nullable()->after('created_at_unix');
        });

        // Paso 3: Migrar datos existentes
        DB::statement('UPDATE time_entries SET entrada_unix = UNIX_TIMESTAMP(entrada)');
        DB::statement('UPDATE time_entries SET salida_unix = UNIX_TIMESTAMP(salida) WHERE salida IS NOT NULL');
        DB::statement('UPDATE time_entries SET created_at_unix = COALESCE(UNIX_TIMESTAMP(created_at), UNIX_TIMESTAMP(entrada))');
        DB::statement('UPDATE time_entries SET updated_at_unix = COALESCE(UNIX_TIMESTAMP(updated_at), UNIX_TIMESTAMP(created_at), UNIX_TIMESTAMP(entrada))');
        // Asegurar que no queden NULLs
        $now = time();
        DB::statement("UPDATE time_entries SET created_at_unix = {$now} WHERE created_at_unix IS NULL");
        DB::statement("UPDATE time_entries SET updated_at_unix = {$now} WHERE updated_at_unix IS NULL");

        // Paso 4: Eliminar columnas antiguas
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropColumn(['entrada', 'salida', 'created_at', 'updated_at']);
        });

        // Paso 5: Renombrar columnas nuevas
        Schema::table('time_entries', function (Blueprint $table) {
            $table->renameColumn('entrada_unix', 'entrada');
            $table->renameColumn('salida_unix', 'salida');
            $table->renameColumn('created_at_unix', 'created_at');
            $table->renameColumn('updated_at_unix', 'updated_at');
        });

        // Paso 6: Hacer entrada NOT NULL (siempre debe tener valor)
        Schema::table('time_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('entrada')->nullable(false)->change();
            $table->unsignedBigInteger('created_at')->nullable(false)->change();
            $table->unsignedBigInteger('updated_at')->nullable(false)->change();
        });

        // Paso 7: Recrear índices
        Schema::table('time_entries', function (Blueprint $table) {
            $table->index(['user_id', 'salida'], 'idx_user_open_entries');
            $table->index(['entrada'], 'idx_entrada_date');
            $table->index(['salida'], 'idx_salida_date');
            $table->index(['user_id', 'entrada'], 'idx_user_entrada');
            $table->index(['entrada', 'salida'], 'idx_date_range');
            $table->index(['user_id', 'entrada', 'salida'], 'idx_user_time_range');
        });

    }

    /**
     * Reverse the migrations.
     *
     * Convierte los campos de time_entries de BIGINT a TIMESTAMP.
     */
    public function down(): void
    {
        // Paso 1: Eliminar índices (solo los que existan)
        $indexes = ['idx_user_open_entries', 'idx_entrada_date', 'idx_salida_date', 'idx_user_entrada', 'idx_date_range', 'idx_user_time_range'];
        foreach ($indexes as $indexName) {
            $indexExists = DB::select('SHOW INDEXES FROM time_entries WHERE Key_name = ?', [$indexName]);
            if (! empty($indexExists)) {
                Schema::table('time_entries', function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        }

        // Paso 2: Agregar columnas temporales con tipo TIMESTAMP
        Schema::table('time_entries', function (Blueprint $table) {
            $table->timestamp('entrada_ts')->nullable()->after('user_id');
            $table->timestamp('salida_ts')->nullable()->after('entrada_ts');
            $table->timestamp('created_at_ts')->nullable()->after('auto_close_reason');
            $table->timestamp('updated_at_ts')->nullable()->after('created_at_ts');
        });

        // Paso 3: Migrar datos de vuelta
        DB::statement('UPDATE time_entries SET entrada_ts = FROM_UNIXTIME(entrada)');
        DB::statement('UPDATE time_entries SET salida_ts = FROM_UNIXTIME(salida) WHERE salida IS NOT NULL');
        DB::statement('UPDATE time_entries SET created_at_ts = FROM_UNIXTIME(created_at)');
        DB::statement('UPDATE time_entries SET updated_at_ts = FROM_UNIXTIME(updated_at)');

        // Paso 4: Eliminar columnas BIGINT
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropColumn(['entrada', 'salida', 'created_at', 'updated_at']);
        });

        // Paso 5: Renombrar columnas
        Schema::table('time_entries', function (Blueprint $table) {
            $table->renameColumn('entrada_ts', 'entrada');
            $table->renameColumn('salida_ts', 'salida');
            $table->renameColumn('created_at_ts', 'created_at');
            $table->renameColumn('updated_at_ts', 'updated_at');
        });

        // Paso 6: Hacer entrada NOT NULL
        Schema::table('time_entries', function (Blueprint $table) {
            $table->timestamp('entrada')->nullable(false)->change();
        });

        // Paso 7: Recrear índices
        Schema::table('time_entries', function (Blueprint $table) {
            $table->index(['user_id', 'salida'], 'idx_user_open_entries');
            $table->index(['entrada'], 'idx_entrada_date');
            $table->index(['salida'], 'idx_salida_date');
            $table->index(['user_id', 'entrada'], 'idx_user_entrada');
            $table->index(['entrada', 'salida'], 'idx_date_range');
            $table->index(['user_id', 'entrada', 'salida'], 'idx_user_time_range');
        });

    }
};
