<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First: Ensure users with is_admin=true have super_admin role
        if (Schema::hasColumn('users', 'is_admin')) {
            $adminUsers = DB::table('users')->where('is_admin', true)->pluck('id');
            $superAdminRole = DB::table('roles')->where('slug', 'super_admin')->first();

            if ($superAdminRole) {
                foreach ($adminUsers as $userId) {
                    DB::table('user_role')->insertOrIgnore([
                        'user_id' => $userId,
                        'role_id' => $superAdminRole->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Then: Remove the column
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_admin');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('is_active');
        });
    }
};
