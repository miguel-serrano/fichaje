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
     * Convierte los campos de fecha de notifications de TIMESTAMP a BIGINT (Unix timestamp).
     */
    public function up(): void
    {
        // Paso 1: Eliminar foreign key y luego el índice (el FK usa el índice)
        $fkExists = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications' AND CONSTRAINT_NAME = 'notifications_user_id_foreign'");
        if (! empty($fkExists)) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropForeign('notifications_user_id_foreign');
            });
        }

        $indexExists = DB::select("SHOW INDEXES FROM notifications WHERE Key_name = 'notifications_user_id_read_at_index'");
        if (! empty($indexExists)) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropIndex('notifications_user_id_read_at_index');
            });
        }

        // Paso 2: Agregar columnas temporales con tipo BIGINT
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('read_at_unix')->nullable()->after('data');
            $table->unsignedBigInteger('created_at_unix')->nullable()->after('read_at_unix');
            $table->unsignedBigInteger('updated_at_unix')->nullable()->after('created_at_unix');
        });

        // Paso 3: Migrar datos existentes
        DB::statement('UPDATE notifications SET read_at_unix = UNIX_TIMESTAMP(read_at) WHERE read_at IS NOT NULL');
        DB::statement('UPDATE notifications SET created_at_unix = UNIX_TIMESTAMP(created_at) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE notifications SET updated_at_unix = UNIX_TIMESTAMP(updated_at) WHERE updated_at IS NOT NULL');

        // Paso 4: Eliminar columnas antiguas
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['read_at', 'created_at', 'updated_at']);
        });

        // Paso 5: Renombrar columnas nuevas
        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('read_at_unix', 'read_at');
            $table->renameColumn('created_at_unix', 'created_at');
            $table->renameColumn('updated_at_unix', 'updated_at');
        });

        // Paso 6: Hacer campos NOT NULL donde corresponda
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('created_at')->nullable(false)->change();
            $table->unsignedBigInteger('updated_at')->nullable(false)->change();
        });

        // Paso 7: Recrear índice y foreign key
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at'], 'notifications_user_id_read_at_index');
            $table->foreign('user_id', 'notifications_user_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Paso 1: Eliminar foreign key y luego índice
        $fkExists = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications' AND CONSTRAINT_NAME = 'notifications_user_id_foreign'");
        if (! empty($fkExists)) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropForeign('notifications_user_id_foreign');
            });
        }

        $indexExists = DB::select("SHOW INDEXES FROM notifications WHERE Key_name = 'notifications_user_id_read_at_index'");
        if (! empty($indexExists)) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropIndex('notifications_user_id_read_at_index');
            });
        }

        // Paso 2: Agregar columnas temporales con tipo TIMESTAMP
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('read_at_ts')->nullable();
            $table->timestamp('created_at_ts')->nullable();
            $table->timestamp('updated_at_ts')->nullable();
        });

        // Paso 3: Migrar datos de vuelta
        DB::statement('UPDATE notifications SET read_at_ts = FROM_UNIXTIME(read_at) WHERE read_at IS NOT NULL');
        DB::statement('UPDATE notifications SET created_at_ts = FROM_UNIXTIME(created_at) WHERE created_at IS NOT NULL');
        DB::statement('UPDATE notifications SET updated_at_ts = FROM_UNIXTIME(updated_at) WHERE updated_at IS NOT NULL');

        // Paso 4: Eliminar columnas BIGINT
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['read_at', 'created_at', 'updated_at']);
        });

        // Paso 5: Renombrar columnas
        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('read_at_ts', 'read_at');
            $table->renameColumn('created_at_ts', 'created_at');
            $table->renameColumn('updated_at_ts', 'updated_at');
        });

        // Paso 6: Recrear índice y foreign key
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at'], 'notifications_user_id_read_at_index');
            $table->foreign('user_id', 'notifications_user_id_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
