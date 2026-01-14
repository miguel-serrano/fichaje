<?php

namespace App\DDD\User\Application\Command;

use App\DDD\User\Domain\ValueObjects\UserId;

final class DeleteUserCommand
{
    private function __construct(
        public readonly UserId $authenticatedUserId,
        public readonly UserId $targetUserId,
    ) {
    }

    public static function create(int $authenticatedUserId, int $targetUserId): self
    {
        return new self(
            authenticatedUserId: UserId::make($authenticatedUserId),
            targetUserId: UserId::make($targetUserId),
        );
    }
}
