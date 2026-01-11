<?php

namespace App\DDD\User\Application\Command;

final class DeleteUserCommand
{
    public function __construct(
        public int $authenticatedUserId,
        public int $targetUserId,
    ) {
    }
}
