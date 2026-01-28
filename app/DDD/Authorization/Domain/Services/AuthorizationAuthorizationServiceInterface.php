<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Domain\Services;

use App\DDD\User\Domain\Entity\User;

interface AuthorizationAuthorizationServiceInterface
{
    public function assertCanManageRoles(User $user): void;

    public function assertCanManagePermissions(User $user): void;

    public function assertCanAssignRoles(User $user): void;
}
