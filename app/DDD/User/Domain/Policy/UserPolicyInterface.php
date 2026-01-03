<?php

namespace App\DDD\User\Domain\Policy;

use App\DDD\User\Domain\Entity\User;

interface UserPolicyInterface
{
    public function canToggleActive(User $authenticatedUser): bool;

    public function canView(User $authenticatedUser, User $targetUser): bool;

    public function canCreate(User $authenticatedUser): bool;

    public function canUpdate(User $authenticatedUser, User $targetUser): bool;

    public function canDelete(User $authenticatedUser, User $targetUser): bool;

    public function canList(User $authenticatedUser): bool;
}
