<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hace los campos created_at y updated_at nullable en tablas pivot.
 *
 * Las relaciones BelongsToMany de Laravel no insertan timestamps automáticamente
 * a menos que se use withTimestamps(). Hacerlos nullable evita errores.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // role_permission: hacer timestamps nullable
        DB::statement('ALTER TABLE role_permission
            MODIFY created_at INT UNSIGNED NULL,
            MODIFY updated_at INT UNSIGNED NULL');

        // user_role: hacer timestamps nullable
        DB::statement('ALTER TABLE user_role
            MODIFY created_at INT UNSIGNED NULL,
            MODIFY updated_at INT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primero establecer valores por defecto donde sea NULL
        $now = time();
        DB::statement("UPDATE role_permission SET created_at = {$now} WHERE created_at IS NULL");
        DB::statement("UPDATE role_permission SET updated_at = {$now} WHERE updated_at IS NULL");
        DB::statement("UPDATE user_role SET created_at = {$now} WHERE created_at IS NULL");
        DB::statement("UPDATE user_role SET updated_at = {$now} WHERE updated_at IS NULL");

        // role_permission: hacer timestamps NOT NULL
        DB::statement('ALTER TABLE role_permission
            MODIFY created_at INT UNSIGNED NOT NULL,
            MODIFY updated_at INT UNSIGNED NOT NULL');

        // user_role: hacer timestamps NOT NULL
        DB::statement('ALTER TABLE user_role
            MODIFY created_at INT UNSIGNED NOT NULL,
            MODIFY updated_at INT UNSIGNED NOT NULL');
    }
};
