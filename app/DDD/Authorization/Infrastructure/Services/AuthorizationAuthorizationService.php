<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Infrastructure\Services;

use App\DDD\Authorization\Domain\Exceptions\UnauthorizedAuthorizationException;
use App\DDD\Authorization\Domain\Policy\AuthorizationPolicy;
use App\DDD\Authorization\Domain\Services\AuthorizationAuthorizationServiceInterface;
use App\DDD\User\Domain\Entity\User;

final class AuthorizationAuthorizationService implements AuthorizationAuthorizationServiceInterface
{
    public function __construct(
        private readonly AuthorizationPolicy $policy,
    ) {
    }

    public function assertCanManageRoles(User $user): void
    {
        if (!$this->policy->canManageRoles($user)) {
            throw UnauthorizedAuthorizationException::forManageRoles();
        }
    }

    public function assertCanManagePermissions(User $user): void
    {
        if (!$this->policy->canManagePermissions($user)) {
            throw UnauthorizedAuthorizationException::forManagePermissions();
        }
    }

    public function assertCanAssignRoles(User $user): void
    {
        if (!$this->policy->canAssignRoles($user)) {
            throw UnauthorizedAuthorizationException::forAssignRoles();
        }
    }
}
