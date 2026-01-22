<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Convierte campos de fecha de BIGINT a INT UNSIGNED.
 *
 * INT UNSIGNED soporta timestamps hasta el año 2106, suficiente para la aplicación.
 * Reduce el tamaño de 8 bytes a 4 bytes por campo.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // time_entries
        DB::statement('ALTER TABLE time_entries
            MODIFY entrada INT UNSIGNED NOT NULL,
            MODIFY salida INT UNSIGNED NULL,
            MODIFY created_at INT UNSIGNED NOT NULL,
            MODIFY updated_at INT UNSIGNED NOT NULL');

        // holiday_requests
        DB::statement('ALTER TABLE holiday_requests
            MODIFY start_date INT UNSIGNED NOT NULL,
            MODIFY end_date INT UNSIGNED NOT NULL,
            MODIFY created_at INT UNSIGNED NOT NULL,
            MODIFY updated_at INT UNSIGNED NOT NULL');

        // users
        DB::statement('ALTER TABLE users
            MODIFY created_at INT UNSIGNED NOT NULL,
            MODIFY updated_at INT UNSIGNED NOT NULL');

        // notifications
        DB::statement('ALTER TABLE notifications
            MODIFY read_at INT UNSIGNED NULL,
            MODIFY created_at INT UNSIGNED NOT NULL,
            MODIFY updated_at INT UNSIGNED NOT NULL');

        // roles
        DB::statement('ALTER TABLE roles
            MODIFY created_at INT UNSIGNED NOT NULL,
            MODIFY updated_at INT UNSIGNED NOT NULL');

        // permissions
        DB::statement('ALTER TABLE permissions
            MODIFY created_at INT UNSIGNED NOT NULL,
            MODIFY updated_at INT UNSIGNED NOT NULL');

        // role_permission
        DB::statement('ALTER TABLE role_permission
            MODIFY created_at INT UNSIGNED NOT NULL,
            MODIFY updated_at INT UNSIGNED NOT NULL');

        // user_role
        DB::statement('ALTER TABLE user_role
            MODIFY created_at INT UNSIGNED NOT NULL,
            MODIFY updated_at INT UNSIGNED NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // time_entries
        DB::statement('ALTER TABLE time_entries
            MODIFY entrada BIGINT UNSIGNED NOT NULL,
            MODIFY salida BIGINT UNSIGNED NULL,
            MODIFY created_at BIGINT UNSIGNED NOT NULL,
            MODIFY updated_at BIGINT UNSIGNED NOT NULL');

        // holiday_requests
        DB::statement('ALTER TABLE holiday_requests
            MODIFY start_date BIGINT UNSIGNED NOT NULL,
            MODIFY end_date BIGINT UNSIGNED NOT NULL,
            MODIFY created_at BIGINT UNSIGNED NOT NULL,
            MODIFY updated_at BIGINT UNSIGNED NOT NULL');

        // users
        DB::statement('ALTER TABLE users
            MODIFY created_at BIGINT UNSIGNED NOT NULL,
            MODIFY updated_at BIGINT UNSIGNED NOT NULL');

        // notifications
        DB::statement('ALTER TABLE notifications
            MODIFY read_at BIGINT UNSIGNED NULL,
            MODIFY created_at BIGINT UNSIGNED NOT NULL,
            MODIFY updated_at BIGINT UNSIGNED NOT NULL');

        // roles
        DB::statement('ALTER TABLE roles
            MODIFY created_at BIGINT UNSIGNED NOT NULL,
            MODIFY updated_at BIGINT UNSIGNED NOT NULL');

        // permissions
        DB::statement('ALTER TABLE permissions
            MODIFY created_at BIGINT UNSIGNED NOT NULL,
            MODIFY updated_at BIGINT UNSIGNED NOT NULL');

        // role_permission
        DB::statement('ALTER TABLE role_permission
            MODIFY created_at BIGINT UNSIGNED NOT NULL,
            MODIFY updated_at BIGINT UNSIGNED NOT NULL');

        // user_role
        DB::statement('ALTER TABLE user_role
            MODIFY created_at BIGINT UNSIGNED NOT NULL,
            MODIFY updated_at BIGINT UNSIGNED NOT NULL');
    }
};
