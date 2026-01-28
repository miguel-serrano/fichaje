<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Infrastructure\Services;

use App\DDD\Holiday\Domain\Exceptions\UnauthorizedHolidayException;
use App\DDD\Holiday\Domain\Policy\HolidayPolicy;
use App\DDD\Holiday\Domain\Services\HolidayAuthorizationServiceInterface;
use App\DDD\User\Domain\Entity\User;

final class HolidayAuthorizationService implements HolidayAuthorizationServiceInterface
{
    public function __construct(
        private readonly HolidayPolicy $policy,
    ) {
    }

    public function assertCanRequestHoliday(User $user): void
    {
        if (!$this->policy->canRequestHoliday($user)) {
            throw UnauthorizedHolidayException::forRequest();
        }
    }

    public function assertCanViewOwnHolidays(User $user): void
    {
        if (!$this->policy->canViewOwnHolidays($user)) {
            throw UnauthorizedHolidayException::forViewOwn();
        }
    }

    public function assertCanViewPendingHolidays(User $user): void
    {
        if (!$this->policy->canViewPendingHolidays($user)) {
            throw UnauthorizedHolidayException::forViewPending();
        }
    }

    public function assertCanViewApprovedHolidays(User $user): void
    {
        if (!$this->policy->canViewApprovedHolidays($user)) {
            throw UnauthorizedHolidayException::forViewApproved();
        }
    }

    public function assertCanApproveHoliday(User $user): void
    {
        if (!$this->policy->canApproveHoliday($user)) {
            throw UnauthorizedHolidayException::forApprove();
        }
    }

    public function assertCanRejectHoliday(User $user): void
    {
        if (!$this->policy->canRejectHoliday($user)) {
            throw UnauthorizedHolidayException::forReject();
        }
    }
}
