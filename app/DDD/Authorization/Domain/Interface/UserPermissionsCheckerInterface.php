<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Domain\Interface;

interface UserPermissionsCheckerInterface
{
    /**
     * Comprueba si el permiso está asignado al usuario a través de alguno de sus roles.
     * No aplica bypass de super_admin: esa política la deciden los voters.
     */
    public function hasRolePermission(int $userId, string $permissionSlug): bool;

    public function isSuperAdmin(int $userId): bool;
}
