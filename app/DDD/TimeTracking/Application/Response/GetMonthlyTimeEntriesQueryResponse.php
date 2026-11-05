<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Response;

use App\DDD\TimeTracking\Domain\Entity\TimeEntry;

final class GetMonthlyTimeEntriesQueryResponse
{
    /**
     * @param TimeEntry[] $timeEntries
     */
    public function __construct(
        private array $timeEntries,
    ) {
    }

    /** @return TimeEntry[] */
    public function timeEntries(): array
    {
        return $this->timeEntries;
    }
}
