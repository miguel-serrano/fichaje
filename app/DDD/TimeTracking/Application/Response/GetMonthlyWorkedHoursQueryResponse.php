<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Response;

final class GetMonthlyWorkedHoursQueryResponse
{
    public function __construct(
        private int $seconds,
    ) {
    }

    public function seconds(): int
    {
        return $this->seconds;
    }

    public function hours(): float
    {
        return round($this->seconds / 3600, 1);
    }
}
