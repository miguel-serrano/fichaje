<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Domain\Interface;

interface UserPermissionsCheckerInterface
{
    public function hasPermission(int $userId, string $permissionSlug): bool;

    public function isSuperAdmin(int $userId): bool;
}
