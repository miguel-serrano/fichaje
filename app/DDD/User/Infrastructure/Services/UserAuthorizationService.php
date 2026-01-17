<?php

namespace App\DDD\User\Infrastructure\Services;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\DDD\User\Domain\Policy\UserPolicy;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;

final class UserAuthorizationService implements UserAuthorizationServiceInterface
{
    public function __construct(private readonly UserPolicy $userPolicy)
    {
    }

    public function assertCanView(User $authenticatedUser, User $targetUser): void
    {
        if (!$this->userPolicy->canView($authenticatedUser, $targetUser)) {
            throw UnauthorizedException::forView();
        }
    }

    public function assertCanToggleActive(User $authenticatedUser): void
    {
        if (!$this->userPolicy->canToggleActive($authenticatedUser)) {
            throw UnauthorizedException::forToggleActive();
        }
    }

    public function assertCanDelete(User $authenticatedUser, User $targetUser): void
    {
        if (!$this->userPolicy->canDelete($authenticatedUser, $targetUser)) {
            throw UnauthorizedException::forDelete();
        }
    }

    public function assertCanList(User $authenticatedUser): void
    {
        if (!$this->userPolicy->canList($authenticatedUser)) {
            throw UnauthorizedException::forList();
        }
    }
}
