<?php

namespace App\DDD\User\Application\Query;

use App\DDD\User\Domain\ValueObjects\UserId;

final class GetAllUsersQuery
{
    private function __construct(
        public readonly UserId $authenticatedUserId,
    ) {
    }

    public static function create(int $authenticatedUserId): self
    {
        return new self(
            authenticatedUserId: UserId::make($authenticatedUserId),
        );
    }
}
