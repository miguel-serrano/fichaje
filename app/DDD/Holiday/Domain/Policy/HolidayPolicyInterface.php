<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Policy;

use App\DDD\User\Domain\Entity\User;

interface HolidayPolicyInterface
{
    public function canRequestHoliday(User $user): bool;

    public function canViewOwnHolidays(User $user): bool;

    public function canViewPendingHolidays(User $user): bool;

    public function canViewApprovedHolidays(User $user): bool;

    public function canApproveHoliday(User $user): bool;

    public function canRejectHoliday(User $user): bool;
}
