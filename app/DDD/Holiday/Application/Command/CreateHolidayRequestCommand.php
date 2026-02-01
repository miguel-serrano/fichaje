<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Command;

use App\DDD\Holiday\Domain\ValueObjects\DateRange;
use App\DDD\User\Domain\ValueObjects\UserId;

final class CreateHolidayRequestCommand
{
    private function __construct(
        public readonly UserId $userId,
        public readonly DateRange $dateRange,
    ) {
    }

    public static function create(int $userId, string $startDate, string $endDate): self
    {
        return new self(
            userId: UserId::make($userId),
            dateRange: DateRange::fromStrings($startDate, $endDate),
        );
    }
}
