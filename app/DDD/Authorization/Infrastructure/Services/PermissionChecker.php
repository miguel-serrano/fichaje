<?php

namespace App\DDD\Authorization\Infrastructure\Services;

use App\DDD\Authorization\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;

class PermissionChecker implements PermissionCheckerInterface
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
        private RoleRepositoryInterface $roleRepository,
    ) {
    }

    public function hasPermission(User $user, string $permissionSlug): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (!$user->id()) {
            return false;
        }

        return $this->permissionRepository->userHasPermission($user->id(), $permissionSlug);
    }

    public function assertHasPermission(User $user, string $permissionSlug): void
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

        return $this->roleRepository->userHasRole($user->id(), $roleSlug);
    }

    public function isSuperAdmin(User $user): bool
    {
        return $this->hasRole($user, 'super_admin');
    }
}
