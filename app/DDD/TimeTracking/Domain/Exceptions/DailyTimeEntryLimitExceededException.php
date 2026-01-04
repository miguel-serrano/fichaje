<?php

namespace App\DDD\TimeTracking\Domain\Exceptions;

use Exception;

class DailyTimeEntryLimitExceededException extends Exception
{
    public const MAX_DAILY_ENTRIES = 8;

    public function __construct(int $currentCount)
    {
        parent::__construct(
            'Has alcanzado el límite máximo de '.self::MAX_DAILY_ENTRIES." fichajes diarios. Registros de hoy: {$currentCount}."
        );
    }
}
