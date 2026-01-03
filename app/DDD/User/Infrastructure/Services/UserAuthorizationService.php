<?php

namespace App\DDD\User\Infrastructure\Services;

use App\DDD\User\Domain\Entity\User as DomainUser;
use App\DDD\User\Domain\Policy\UserPolicy;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;
use App\Models\User as EloquentUser;

final class UserAuthorizationService implements UserAuthorizationServiceInterface
{
    public function __construct(private readonly UserPolicy $userPolicy) {}

    public function ensureCanToggleActive(EloquentUser $authenticatedUser): void
    {
        $domainUser = $this->toDomainUser($authenticatedUser);
        $this->userPolicy->ensureCanToggleActive($domainUser);
    }

    public function ensureCanView(EloquentUser $authenticatedUser, EloquentUser $targetUser): void
    {
        $domainAuthUser = $this->toDomainUser($authenticatedUser);
        $domainTargetUser = $this->toDomainUser($targetUser);
        $this->userPolicy->ensureCanView($domainAuthUser, $domainTargetUser);
    }

    public function ensureCanCreate(EloquentUser $authenticatedUser): void
    {
        $domainUser = $this->toDomainUser($authenticatedUser);
        $this->userPolicy->ensureCanCreate($domainUser);
    }

    public function ensureCanUpdate(EloquentUser $authenticatedUser, EloquentUser $targetUser): void
    {
        $domainAuthUser = $this->toDomainUser($authenticatedUser);
        $domainTargetUser = $this->toDomainUser($targetUser);
        $this->userPolicy->ensureCanUpdate($domainAuthUser, $domainTargetUser);
    }

    public function ensureCanDelete(EloquentUser $authenticatedUser, EloquentUser $targetUser): void
    {
        $domainAuthUser = $this->toDomainUser($authenticatedUser);
        $domainTargetUser = $this->toDomainUser($targetUser);
        $this->userPolicy->ensureCanDelete($domainAuthUser, $domainTargetUser);
    }

    private function toDomainUser(EloquentUser $eloquentUser): DomainUser
    {
        return DomainUser::fromPrimitives(
            $eloquentUser->id,
            $eloquentUser->uuid,
            $eloquentUser->email,
            $eloquentUser->name,
            $eloquentUser->is_active ?? true,
            $eloquentUser->remember_token ?? null
        );
    }
}
