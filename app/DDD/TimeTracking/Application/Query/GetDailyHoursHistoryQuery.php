<?php

namespace App\DDD\TimeTracking\Application\Query;

use App\DDD\User\Domain\ValueObjects\UserId;

final class GetDailyHoursHistoryQuery
{
    private function __construct(
        public readonly UserId $authenticatedUserId,
        public readonly UserId $targetUserId,
        public readonly int $days,
    ) {
    }

    public static function create(int $authenticatedUserId, int $targetUserId, int $days = 30): self
    {
        return new self(
            authenticatedUserId: UserId::make($authenticatedUserId),
            targetUserId: UserId::make($targetUserId),
            days: $days,
        );
    }
}
