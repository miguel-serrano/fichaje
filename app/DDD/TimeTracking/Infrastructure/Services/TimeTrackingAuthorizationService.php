<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Infrastructure\Services;

use App\DDD\TimeTracking\Domain\Exceptions\UnauthorizedTimeTrackingException;
use App\DDD\TimeTracking\Domain\Policy\TimeTrackingPolicy;
use App\DDD\TimeTracking\Domain\Services\TimeTrackingAuthorizationServiceInterface;
use App\DDD\User\Domain\Entity\User;

final class TimeTrackingAuthorizationService implements TimeTrackingAuthorizationServiceInterface
{
    public function __construct(
        private readonly TimeTrackingPolicy $policy,
    ) {
    }

    public function assertCanClockIn(User $user): void
    {
        if (!$this->policy->canClockIn($user)) {
            throw UnauthorizedTimeTrackingException::forClockIn();
        }
    }

    public function assertCanClockOut(User $user): void
    {
        if (!$this->policy->canClockOut($user)) {
            throw UnauthorizedTimeTrackingException::forClockOut();
        }
    }

    public function assertCanViewTimeEntry(User $user): void
    {
        if (!$this->policy->canViewTimeEntry($user)) {
            throw UnauthorizedTimeTrackingException::forView();
        }
    }

    public function assertCanCloseOrphanEntries(User $user): void
    {
        if (!$this->policy->canCloseOrphanEntries($user)) {
            throw UnauthorizedTimeTrackingException::forCloseOrphanEntries();
        }
    }

    public function assertCanViewReports(User $user): void
    {
        if (!$this->policy->canViewReports($user)) {
            throw UnauthorizedTimeTrackingException::forReports();
        }
    }
}
