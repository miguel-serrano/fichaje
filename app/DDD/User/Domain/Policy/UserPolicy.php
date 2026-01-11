<?php

namespace App\DDD\User\Domain\Policy;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;

final class UserPolicy implements UserPolicyInterface
{
    public function canToggleActive(User $authenticatedUser): bool
    {
        return $authenticatedUser->isAdmin();
    }

    public function canView(User $authenticatedUser, User $targetUser): bool
    {
        return $authenticatedUser->isAdmin() || $authenticatedUser->id()?->value() === $targetUser->id()?->value();
    }

    public function canCreate(User $authenticatedUser): bool
    {
        return $authenticatedUser->isAdmin();
    }

    public function canUpdate(User $authenticatedUser, User $targetUser): bool
    {
        return $authenticatedUser->isAdmin();
    }

    public function canDelete(User $authenticatedUser, User $targetUser): bool
    {
        return $authenticatedUser->isAdmin() && !$targetUser->isAdmin();
    }

    public function canList(User $authenticatedUser): bool
    {
        return $authenticatedUser->isAdmin();
    }

    public function ensureCanToggleActive(User $authenticatedUser): void
    {
        if (!$this->canToggleActive($authenticatedUser)) {
            throw UnauthorizedException::forToggleActive();
        }
    }

    public function ensureCanView(User $authenticatedUser, User $targetUser): void
    {
        if (!$this->canView($authenticatedUser, $targetUser)) {
            throw UnauthorizedException::forView();
        }
    }

    public function ensureCanCreate(User $authenticatedUser): void
    {
        if (!$this->canCreate($authenticatedUser)) {
            throw UnauthorizedException::forCreate();
        }
    }

    public function ensureCanUpdate(User $authenticatedUser, User $targetUser): void
    {
        if (!$this->canUpdate($authenticatedUser, $targetUser)) {
            throw UnauthorizedException::forUpdate();
        }
    }

    public function ensureCanDelete(User $authenticatedUser, User $targetUser): void
    {
        if (!$this->canDelete($authenticatedUser, $targetUser)) {
            throw UnauthorizedException::forDelete();
        }
    }

    public function ensureCanList(User $authenticatedUser): void
    {
        if (!$this->canList($authenticatedUser)) {
            throw UnauthorizedException::forList();
        }
    }
}
