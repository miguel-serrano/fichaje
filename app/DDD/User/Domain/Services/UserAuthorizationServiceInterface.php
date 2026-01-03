<?php

namespace App\DDD\User\Domain\Services;

use App\Models\User as EloquentUser;

interface UserAuthorizationServiceInterface
{
    public function ensureCanToggleActive(EloquentUser $authenticatedUser): void;

    public function ensureCanView(EloquentUser $authenticatedUser, EloquentUser $targetUser): void;

    public function ensureCanCreate(EloquentUser $authenticatedUser): void;

    public function ensureCanUpdate(EloquentUser $authenticatedUser, EloquentUser $targetUser): void;

    public function ensureCanDelete(EloquentUser $authenticatedUser, EloquentUser $targetUser): void;

    public function ensureCanList(EloquentUser $authenticatedUser): void;
}
