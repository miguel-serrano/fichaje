<?php

namespace App\DDD\User\Domain\Services;

use App\DDD\User\Domain\Entity\User;

interface UserAuthorizationServiceInterface
{
    public function ensureCanToggleActive(User $authenticatedUser): void;

    public function ensureCanView(User $authenticatedUser, User $targetUser): void;

    public function ensureCanCreate(User $authenticatedUser): void;

    public function ensureCanUpdate(User $authenticatedUser, User $targetUser): void;

    public function ensureCanDelete(User $authenticatedUser, User $targetUser): void;

    public function ensureCanList(User $authenticatedUser): void;
}
