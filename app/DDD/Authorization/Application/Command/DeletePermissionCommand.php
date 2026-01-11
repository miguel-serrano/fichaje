<?php

namespace App\DDD\Authorization\Application\Command;

final class DeletePermissionCommand
{
    public function __construct(
        public int $authenticatedUserId,
        public int $permissionId,
    ) {
    }
}
