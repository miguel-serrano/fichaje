<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Command;

final class CreateHolidayRequestCommand
{
    public function __construct(
        public int $userId,
        public string $startDate,
        public string $endDate,
    ) {
    }
}
