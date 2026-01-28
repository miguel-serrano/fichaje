<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Domain\Policy;

use App\DDD\User\Domain\Entity\User;

interface AuthorizationPolicyInterface
{
    public function canManageRoles(User $user): bool;

    public function canManagePermissions(User $user): bool;

    public function canAssignRoles(User $user): bool;
}
