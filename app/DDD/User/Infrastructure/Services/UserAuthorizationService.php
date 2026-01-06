<?php

namespace App\DDD\User\Infrastructure\Services;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Policy\UserPolicy;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;

final class UserAuthorizationService implements UserAuthorizationServiceInterface
{
    public function __construct(private readonly UserPolicy $userPolicy) {}

    public function ensureCanToggleActive(User $authenticatedUser): void
    {
        $this->userPolicy->ensureCanToggleActive($authenticatedUser);
    }

    public function ensureCanView(User $authenticatedUser, User $targetUser): void
    {
        $this->userPolicy->ensureCanView($authenticatedUser, $targetUser);
    }

    public function ensureCanCreate(User $authenticatedUser): void
    {
        $this->userPolicy->ensureCanCreate($authenticatedUser);
    }

    public function ensureCanUpdate(User $authenticatedUser, User $targetUser): void
    {
        $this->userPolicy->ensureCanUpdate($authenticatedUser, $targetUser);
    }

    public function ensureCanDelete(User $authenticatedUser, User $targetUser): void
    {
        $this->userPolicy->ensureCanDelete($authenticatedUser, $targetUser);
    }

    public function ensureCanList(User $authenticatedUser): void
    {
        $this->userPolicy->ensureCanList($authenticatedUser);
    }
}
