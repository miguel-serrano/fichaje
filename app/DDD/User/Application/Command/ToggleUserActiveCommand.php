<?php

namespace App\DDD\User\Application\Command;

final class ToggleUserActiveCommand
{
    public function __construct(
        public int $authenticatedUserId,
        public int $targetUserId
    ) {}
}
