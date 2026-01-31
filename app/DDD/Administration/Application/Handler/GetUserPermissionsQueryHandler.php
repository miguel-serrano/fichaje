<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Query\GetUserPermissionsQuery;
use App\Models\Permission as PermissionModel;
use App\Models\Role as RoleModel;
use App\Models\RolePermission;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;

class GetUserPermissionsQueryHandler
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {}

    /**
     * @return string[]
     */
    public function handle(GetUserPermissionsQuery $query): array
    {
        $userRoleTable = UserRole::tableName();
        $rolesTable = RoleModel::tableName();
        $rolePermissionTable = RolePermission::tableName();
        $permissionsTable = PermissionModel::tableName();

        $hasSuperAdmin = $this->connection->table($userRoleTable)
            ->join($rolesTable, "{$userRoleTable}.role_id", '=', "{$rolesTable}.id")
            ->where("{$userRoleTable}.user_id", $query->userId)
            ->where("{$rolesTable}.slug", 'super_admin')
            ->exists();

        if ($hasSuperAdmin) {
            return $this->connection->table($permissionsTable)
                ->pluck('slug')
                ->toArray();
        }

        return $this->connection->table($userRoleTable)
            ->join($rolePermissionTable, "{$userRoleTable}.role_id", '=', "{$rolePermissionTable}.role_id")
            ->join($permissionsTable, "{$rolePermissionTable}.permission_id", '=', "{$permissionsTable}.id")
            ->where("{$userRoleTable}.user_id", $query->userId)
            ->distinct()
            ->pluck("{$permissionsTable}.slug")
            ->toArray();
    }
}
