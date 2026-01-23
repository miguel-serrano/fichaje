<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Services;

use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
use App\DDD\TimeTracking\Domain\Exceptions\DailyTimeEntryLimitExceededException;

final class DailyLimitValidatorService
{
    public const MAX_DAILY_ENTRIES = 8;

    private function __construct(
        private TimeEntryCalculationService $calculationService,
    ) {
    }

    public static function create(TimeEntryCalculationService $calculationService): self
    {
        return new self($calculationService);
    }

    /**
     * @param TimeEntry[] $timeEntries
     *
     * @throws DailyTimeEntryLimitExceededException
     */
    public function ensureDailyLimitNotExceeded(array $timeEntries): void
    {
        $todayEntries = $this->calculationService->countTodayEntries($timeEntries);

        if ($todayEntries >= self::MAX_DAILY_ENTRIES) {
            throw DailyTimeEntryLimitExceededException::withCount($todayEntries);
        }
    }

    /**
     * @param TimeEntry[] $timeEntries
     */
    public function canCreateNewEntry(array $timeEntries): bool
    {
        $todayEntries = $this->calculationService->countTodayEntries($timeEntries);

        return $todayEntries < self::MAX_DAILY_ENTRIES;
    }

    /**
     * @param TimeEntry[] $timeEntries
     */
    public function remainingEntriesForToday(array $timeEntries): int
    {
        $todayEntries = $this->calculationService->countTodayEntries($timeEntries);

        return max(0, self::MAX_DAILY_ENTRIES - $todayEntries);
    }
}
