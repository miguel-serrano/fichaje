<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Infrastructure\Service;

use App\DDD\Authorization\Domain\Interface\UserPermissionsCheckerInterface;
use App\Models\Permission as PermissionModel;
use App\Models\Role as RoleModel;
use App\Models\RolePermission;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;

final class UserPermissionsChecker implements UserPermissionsCheckerInterface
{
    private string $permissionTable;

    private string $roleTable;

    private string $userRoleTable;

    private string $rolePermissionTable;

    public function __construct(
        private readonly ConnectionInterface $connection,
    ) {
        $this->permissionTable = PermissionModel::tableName();
        $this->roleTable = RoleModel::tableName();
        $this->userRoleTable = UserRole::tableName();
        $this->rolePermissionTable = RolePermission::tableName();
    }

    public function hasPermission(int $userId, string $permissionSlug): bool
    {
        if ($this->isSuperAdmin($userId)) {
            return true;
        }

        return $this->connection->table($this->userRoleTable)
            ->join($this->rolePermissionTable, "{$this->userRoleTable}.role_id", '=', "{$this->rolePermissionTable}.role_id")
            ->join($this->permissionTable, "{$this->rolePermissionTable}.permission_id", '=', "{$this->permissionTable}.id")
            ->where("{$this->userRoleTable}.user_id", $userId)
            ->where("{$this->permissionTable}.slug", $permissionSlug)
            ->exists();
    }

    public function isSuperAdmin(int $userId): bool
    {
        return $this->connection->table($this->userRoleTable)
            ->join($this->roleTable, "{$this->userRoleTable}.role_id", '=', "{$this->roleTable}.id")
            ->where("{$this->userRoleTable}.user_id", $userId)
            ->where("{$this->roleTable}.slug", 'super_admin')
            ->exists();
    }
}
