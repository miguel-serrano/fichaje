<?php

namespace App\DDD\Authorization\Application\Command;

final class DeleteRoleCommand
{
    public function __construct(
        public int $authenticatedUserId,
        public int $roleId,
    ) {
    }
}
