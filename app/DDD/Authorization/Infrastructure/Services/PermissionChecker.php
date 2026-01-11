<?php

namespace App\DDD\Authorization\Infrastructure\Services;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\DB;

class PermissionChecker implements PermissionCheckerInterface
{
    public function hasPermission(User $user, string $permissionSlug): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (!$user->id()) {
            return false;
        }

        return DB::table('user_role')
            ->join('role_permission', 'user_role.role_id', '=', 'role_permission.role_id')
            ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
            ->where('user_role.user_id', $user->id()->value())
            ->where('permissions.slug', $permissionSlug)
            ->exists();
    }

    public function ensureHasPermission(User $user, string $permissionSlug): void
    {
        if (!$this->hasPermission($user, $permissionSlug)) {
            throw UnauthorizedException::forPermission($permissionSlug);
        }
    }

    public function hasRole(User $user, string $roleSlug): bool
    {
        if (!$user->id()) {
            return false;
        }

        return DB::table('user_role')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->where('user_role.user_id', $user->id()->value())
            ->where('roles.slug', $roleSlug)
            ->exists();
    }

    public function isSuperAdmin(User $user): bool
    {
        return $this->hasRole($user, 'super_admin');
    }
}
