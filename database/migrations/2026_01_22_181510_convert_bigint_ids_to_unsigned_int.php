<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Convierte campos ID de BIGINT a INT UNSIGNED.
 *
 * INT UNSIGNED soporta hasta 4.294.967.295 registros, suficiente para la aplicación.
 * Reduce el tamaño de 8 bytes a 4 bytes por campo.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Eliminar foreign keys existentes
        $this->dropForeignKeyIfExists('time_entries', 'time_entries_user_id_foreign');
        $this->dropForeignKeyIfExists('holiday_requests', 'holiday_requests_user_id_foreign');
        $this->dropForeignKeyIfExists('notifications', 'notifications_user_id_foreign');
        $this->dropForeignKeyIfExists('role_permission', 'role_permission_role_id_foreign');
        $this->dropForeignKeyIfExists('role_permission', 'role_permission_permission_id_foreign');
        $this->dropForeignKeyIfExists('user_role', 'user_role_user_id_foreign');
        $this->dropForeignKeyIfExists('user_role', 'user_role_role_id_foreign');

        // 2. Modificar tipos de datos
        DB::statement('ALTER TABLE users MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE time_entries MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT, MODIFY user_id INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE holiday_requests MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT, MODIFY user_id INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE notifications MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT, MODIFY user_id INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE roles MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE permissions MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE role_permission MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT, MODIFY role_id INT UNSIGNED NOT NULL, MODIFY permission_id INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE user_role MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT, MODIFY user_id INT UNSIGNED NOT NULL, MODIFY role_id INT UNSIGNED NOT NULL');

        // 3. Recrear foreign keys
        DB::statement('ALTER TABLE time_entries ADD CONSTRAINT time_entries_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE holiday_requests ADD CONSTRAINT holiday_requests_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE notifications ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE role_permission ADD CONSTRAINT role_permission_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE role_permission ADD CONSTRAINT role_permission_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE user_role ADD CONSTRAINT user_role_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE user_role ADD CONSTRAINT user_role_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Eliminar foreign keys
        $this->dropForeignKeyIfExists('time_entries', 'time_entries_user_id_foreign');
        $this->dropForeignKeyIfExists('holiday_requests', 'holiday_requests_user_id_foreign');
        $this->dropForeignKeyIfExists('notifications', 'notifications_user_id_foreign');
        $this->dropForeignKeyIfExists('role_permission', 'role_permission_role_id_foreign');
        $this->dropForeignKeyIfExists('role_permission', 'role_permission_permission_id_foreign');
        $this->dropForeignKeyIfExists('user_role', 'user_role_user_id_foreign');
        $this->dropForeignKeyIfExists('user_role', 'user_role_role_id_foreign');

        // 2. Modificar tipos de datos a BIGINT
        DB::statement('ALTER TABLE users MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE time_entries MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, MODIFY user_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE holiday_requests MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, MODIFY user_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE notifications MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, MODIFY user_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE roles MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE permissions MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE role_permission MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, MODIFY role_id BIGINT UNSIGNED NOT NULL, MODIFY permission_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE user_role MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, MODIFY user_id BIGINT UNSIGNED NOT NULL, MODIFY role_id BIGINT UNSIGNED NOT NULL');

        // 3. Recrear foreign keys
        DB::statement('ALTER TABLE time_entries ADD CONSTRAINT time_entries_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE holiday_requests ADD CONSTRAINT holiday_requests_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE notifications ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE role_permission ADD CONSTRAINT role_permission_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE role_permission ADD CONSTRAINT role_permission_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE user_role ADD CONSTRAINT user_role_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE user_role ADD CONSTRAINT user_role_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE');
    }

    /**
     * Elimina una foreign key si existe.
     */
    private function dropForeignKeyIfExists(string $table, string $foreignKey): void
    {
        $database = DB::getDatabaseName();
        $exists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = ?
            AND TABLE_NAME = ?
            AND CONSTRAINT_NAME = ?
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$database, $table, $foreignKey]);

        if (! empty($exists)) {
            DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$foreignKey}");
        }
    }
};
