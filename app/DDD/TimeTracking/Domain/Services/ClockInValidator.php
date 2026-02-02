<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Services;

use App\DDD\TimeTracking\Domain\Exceptions\OpenTimeEntryAlreadyExistsException;
use App\DDD\TimeTracking\Domain\Interface\ClockInValidatorInterface;
use App\DDD\User\Domain\Entity\User;

final class ClockInValidator implements ClockInValidatorInterface
{
    public function __construct(
        private TimeEntryCalculationService $calculationService,
        private DailyLimitValidatorService $dailyLimitValidator,
    ) {
    }

    public function ensureCanClockIn(User $user): void
    {
        if ($this->calculationService->hasOpenEntry($user->timeEntries())) {
            throw new OpenTimeEntryAlreadyExistsException();
        }

        if (!$user->isSuperAdmin()) {
            $this->dailyLimitValidator->ensureDailyLimitNotExceeded($user->timeEntries());
        }
    }
}
