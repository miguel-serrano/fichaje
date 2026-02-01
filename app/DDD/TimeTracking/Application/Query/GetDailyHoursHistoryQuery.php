<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Query;

use App\DDD\TimeTracking\Domain\ValueObjects\DaysRange;
use App\DDD\User\Domain\ValueObjects\UserId;

final class GetDailyHoursHistoryQuery
{
    private function __construct(
        public readonly UserId $userId,
        public readonly DaysRange $days,
    ) {
    }

    public static function create(int $userId, int $days = 30): self
    {
        return new self(
            userId: UserId::make($userId),
            days: DaysRange::make($days),
        );
    }
}
