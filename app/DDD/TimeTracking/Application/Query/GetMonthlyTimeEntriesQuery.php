<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Query;

use App\DDD\User\Domain\ValueObjects\UserId;

final class GetMonthlyTimeEntriesQuery
{
    private function __construct(
        public readonly UserId $userId,
        public readonly int $year,
        public readonly int $month,
    ) {
    }

    public static function create(int $userId, int $year, int $month): self
    {
        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException("Mes no válido: {$month}");
        }

        return new self(
            userId: UserId::make($userId),
            year: $year,
            month: $month,
        );
    }
}
