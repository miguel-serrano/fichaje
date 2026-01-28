<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Services;

use App\DDD\User\Domain\Entity\User;

interface HolidayAuthorizationServiceInterface
{
    public function assertCanRequestHoliday(User $user): void;

    public function assertCanViewOwnHolidays(User $user): void;

    public function assertCanViewPendingHolidays(User $user): void;

    public function assertCanViewApprovedHolidays(User $user): void;

    public function assertCanApproveHoliday(User $user): void;

    public function assertCanRejectHoliday(User $user): void;
}
