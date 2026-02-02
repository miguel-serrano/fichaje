<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Services;

use App\DDD\TimeTracking\Domain\Exceptions\NoOpenTimeEntryException;
use App\DDD\TimeTracking\Domain\Interface\ClockOutValidatorInterface;
use App\DDD\User\Domain\Entity\User;

final class ClockOutValidator implements ClockOutValidatorInterface
{
    public function __construct(
        private TimeEntryCalculationService $calculationService,
    ) {
    }

    public function ensureCanClockOut(User $user): void
    {
        if (!$this->calculationService->hasOpenEntry($user->timeEntries())) {
            throw new NoOpenTimeEntryException();
        }
    }
}
