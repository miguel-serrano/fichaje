<?php

namespace App\DDD\Authorization\Domain\Services;

use App\DDD\User\Domain\Entity\User;

interface PermissionCheckerInterface
{
    public function hasPermission(User $user, string $permissionSlug): bool;

    /**
     * @param string[] $permissionSlugs
     */
    public function hasAnyPermission(User $user, array $permissionSlugs): bool;

    /**
     * @param string[] $permissionSlugs
     */
    public function hasAllPermissions(User $user, array $permissionSlugs): bool;

    public function ensureHasPermission(User $user, string $permissionSlug): void;

    public function hasRole(User $user, string $roleSlug): bool;

    public function isSuperAdmin(User $user): bool;
}
