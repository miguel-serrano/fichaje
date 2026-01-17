<?php

namespace App\DDD\User\Domain\Policy;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\User\Domain\Entity\User;

final class UserPolicy implements UserPolicyInterface
{
    public function __construct(
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function canToggleActive(User $authenticatedUser): bool
    {
        return $this->permissionChecker->isSuperAdmin($authenticatedUser);
    }

    public function canView(User $authenticatedUser, User $targetUser): bool
    {
        return $this->permissionChecker->isSuperAdmin($authenticatedUser)
            || $authenticatedUser->id()?->value() === $targetUser->id()?->value();
    }

    public function canDelete(User $authenticatedUser, User $targetUser): bool
    {
        return $this->permissionChecker->isSuperAdmin($authenticatedUser)
            && !$this->permissionChecker->isSuperAdmin($targetUser);
    }

    public function canList(User $authenticatedUser): bool
    {
        return $this->permissionChecker->isSuperAdmin($authenticatedUser);
    }
}
