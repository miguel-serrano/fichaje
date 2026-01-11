<?php

namespace App\DDD\Authorization\Application\Command;

final class UpdatePermissionCommand
{
    public function __construct(
        public int $authenticatedUserId,
        public int $permissionId,
        public string $name,
        public ?string $description,
    ) {
    }
}
