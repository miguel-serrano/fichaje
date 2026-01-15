<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Command;

use App\DDD\User\Domain\ValueObjects\UserId;

final class CreateHolidayRequestCommand
{
    private function __construct(
        public readonly UserId $userId,
        public readonly string $startDate,
        public readonly string $endDate,
    ) {
    }

    public static function create(int $userId, string $startDate, string $endDate): self
    {
        return new self(
            userId: UserId::make($userId),
            startDate: $startDate,
            endDate: $endDate,
        );
    }
}
