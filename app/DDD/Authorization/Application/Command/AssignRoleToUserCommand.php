<?php

namespace App\DDD\Authorization\Application\Command;

final class AssignRoleToUserCommand
{
    public function __construct(
        public int $authenticatedUserId,
        public int $targetUserId,
        public string $roleSlug,
    ) {
    }
}
