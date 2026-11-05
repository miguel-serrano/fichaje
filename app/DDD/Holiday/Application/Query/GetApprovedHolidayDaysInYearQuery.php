<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Query;

use App\DDD\User\Domain\ValueObjects\UserId;

final class GetApprovedHolidayDaysInYearQuery
{
    private function __construct(
        public readonly UserId $userId,
        public readonly int $year,
    ) {
    }

    public static function create(int $userId, int $year): self
    {
        return new self(
            userId: UserId::make($userId),
            year: $year,
        );
    }
}
