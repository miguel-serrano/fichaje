<?php

namespace App\DDD\Authorization\Application\Command;

final class UpdateRoleCommand
{
    public function __construct(
        public int $authenticatedUserId,
        public int $roleId,
        public string $name,
        public ?string $description,
        public int $hierarchy = 0,
    ) {
    }
}
