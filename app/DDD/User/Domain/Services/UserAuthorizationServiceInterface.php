<?php

namespace App\DDD\User\Domain\Services;

use App\DDD\User\Domain\Entity\User;

interface UserAuthorizationServiceInterface
{
    public function assertCanToggleActive(User $authenticatedUser): void;

    public function assertCanView(User $authenticatedUser, User $targetUser): void;

    public function assertCanDelete(User $authenticatedUser, User $targetUser): void;

    public function assertCanList(User $authenticatedUser): void;
}
