<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Command;

final class ApproveHolidayRequestCommand
{
    public function __construct(
        public int $authenticatedUserId,
        public int $holidayRequestId
    ) {}
}
