<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Response;

final class GetApprovedHolidayDaysInYearQueryResponse
{
    public function __construct(
        private int $days,
    ) {
    }

    public function days(): int
    {
        return $this->days;
    }
}
