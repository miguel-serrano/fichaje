<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Query\GetUserPermissionsQuery;
use Illuminate\Support\Facades\DB;

class GetUserPermissionsQueryHandler
{
    /**
     * @return string[]
     */
    public function handle(GetUserPermissionsQuery $query): array
    {
        // Check if user has super_admin role
        $hasSuperAdmin = DB::table('user_role')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->where('user_role.user_id', $query->userId)
            ->where('roles.slug', 'super_admin')
            ->exists();

        if ($hasSuperAdmin) {
            return DB::table('permissions')
                ->pluck('slug')
                ->toArray();
        }

        return DB::table('user_role')
            ->join('role_permission', 'user_role.role_id', '=', 'role_permission.role_id')
            ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
            ->where('user_role.user_id', $query->userId)
            ->distinct()
            ->pluck('permissions.slug')
            ->toArray();
    }
}
