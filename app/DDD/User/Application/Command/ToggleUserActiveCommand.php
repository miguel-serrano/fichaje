<?php

namespace App\DDD\User\Application\Command;

use App\Models\User as EloquentUser;

final class ToggleUserActiveCommand
{
    public function __construct(
        public EloquentUser $authenticatedUser,
        public int $targetUserId
    ) {}
}
