<?php

namespace App\DDD\Authorization\Domain\Services;

use App\DDD\User\Domain\Entity\User;

interface PermissionCheckerInterface
{
    public function hasPermission(User $user, string $permissionSlug): bool;

    public function assertHasPermission(User $user, string $permissionSlug): void;

    public function hasRole(User $user, string $roleSlug): bool;

    public function isSuperAdmin(User $user): bool;
}
