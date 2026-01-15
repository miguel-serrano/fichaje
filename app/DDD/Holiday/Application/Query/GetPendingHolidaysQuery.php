<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Query;

use App\DDD\User\Domain\ValueObjects\UserId;

final class GetPendingHolidaysQuery
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
