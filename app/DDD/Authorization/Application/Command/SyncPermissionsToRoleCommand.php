<?php

namespace App\DDD\Authorization\Application\Command;

final class SyncPermissionsToRoleCommand
{
    /**
     * @param int[] $permissionIds
     */
    public function __construct(
        public int $authenticatedUserId,
        public int $roleId,
        public array $permissionIds,
    ) {
    }
}
