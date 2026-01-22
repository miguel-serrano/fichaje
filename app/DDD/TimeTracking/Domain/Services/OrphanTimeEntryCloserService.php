<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Services;

use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
use App\DDD\TimeTracking\Domain\ValueObjects\OrphanClosureResult;

final class OrphanTimeEntryCloserService
{
    private const MAX_HOURS = 8;

    private const REASON_MAX_HOURS = 'max_hours_exceeded';

    private const REASON_END_OF_DAY = 'end_of_day';

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * Calcula el momento y razón de cierre para una entrada huérfana.
     *
     * @param TimeEntry $entry              La entrada huérfana a procesar
     * @param int       $workedSecondsToday Segundos ya trabajados ese día (excluyendo esta entrada)
     */
    public function calculateClosure(TimeEntry $entry, int $workedSecondsToday): OrphanClosureResult
    {
        $startTime = $entry->startTime();
        $startDate = date('Y-m-d', $startTime);
        $endOfDay = strtotime($startDate.' 23:59:59');
        $maxSecondsDaily = self::MAX_HOURS * 3600;

        $remainingSeconds = $maxSecondsDaily - $workedSecondsToday;

        if ($remainingSeconds <= 0) {
            return OrphanClosureResult::forEntry($entry, $startTime, self::REASON_MAX_HOURS);
        }

        $maxHoursLimit = $startTime + $remainingSeconds;

        if ($maxHoursLimit < $endOfDay) {
            return OrphanClosureResult::forEntry($entry, $maxHoursLimit, self::REASON_MAX_HOURS);
        }

        return OrphanClosureResult::forEntry($entry, $endOfDay, self::REASON_END_OF_DAY);
    }

    public function maxDailySeconds(): int
    {
        return self::MAX_HOURS * 3600;
    }
}
