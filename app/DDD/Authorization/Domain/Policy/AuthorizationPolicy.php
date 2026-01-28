<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Domain\Policy;

use App\DDD\Authorization\Domain\Permission\AuthorizationPermission;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\User\Domain\Entity\User;

final class AuthorizationPolicy implements AuthorizationPolicyInterface
{
    public function __construct(
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function canManageRoles(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, AuthorizationPermission::ManageRoles->value);
    }

    public function canManagePermissions(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, AuthorizationPermission::ManagePermissions->value);
    }

    public function canAssignRoles(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, AuthorizationPermission::AssignRoles->value);
    }
}
