<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Services;

use App\DDD\TimeTracking\Domain\Entity\TimeEntry;

final class TimeEntryCalculationService
{
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * @param TimeEntry[] $timeEntries
     */
    public function calculateAccumulatedSecondsForDate(array $timeEntries, string $date): int
    {
        $total = 0;

        foreach ($timeEntries as $entry) {
            if (date('Y-m-d', $entry->startTime()) === $date) {
                $total += $entry->workedSeconds();
            }
        }

        return $total;
    }

    /**
     * @param TimeEntry[] $timeEntries
     */
    public function calculateTodayAccumulatedSeconds(array $timeEntries): int
    {
        return $this->calculateAccumulatedSecondsForDate($timeEntries, date('Y-m-d'));
    }

    /**
     * @param TimeEntry[] $timeEntries
     */
    public function countEntriesForDate(array $timeEntries, string $date): int
    {
        $count = 0;

        foreach ($timeEntries as $entry) {
            if (date('Y-m-d', $entry->startTime()) === $date) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @param TimeEntry[] $timeEntries
     */
    public function countTodayEntries(array $timeEntries): int
    {
        return $this->countEntriesForDate($timeEntries, date('Y-m-d'));
    }

    /**
     * @param TimeEntry[] $timeEntries
     */
    public function hasOpenEntry(array $timeEntries): bool
    {
        foreach ($timeEntries as $entry) {
            if ($entry->isOpen()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TimeEntry[] $timeEntries
     */
    public function findOpenEntry(array $timeEntries): ?TimeEntry
    {
        foreach ($timeEntries as $entry) {
            if ($entry->isOpen()) {
                return $entry;
            }
        }

        return null;
    }
}
