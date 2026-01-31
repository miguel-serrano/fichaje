<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

final class SyncPermissionsToRoleCommand
{
    /**
     * @param  int[]  $permissionIds
     */
    private function __construct(
        public readonly int $authenticatedUserId,
        public readonly int $roleId,
        public readonly array $permissionIds,
    ) {}

    /**
     * @param  int[]  $permissionIds
     */
    public static function create(int $authenticatedUserId, int $roleId, array $permissionIds): self
    {
        return new self(
            authenticatedUserId: $authenticatedUserId,
            roleId: $roleId,
            permissionIds: $permissionIds,
        );
    }
}
