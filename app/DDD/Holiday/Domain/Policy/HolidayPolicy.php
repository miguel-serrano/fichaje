<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Policy;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\User\Domain\Entity\User;

final class HolidayPolicy implements HolidayPolicyInterface
{
    public function __construct(
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function canRequestHoliday(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, HolidayPermission::Request->value);
    }

    public function canViewOwnHolidays(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, HolidayPermission::ViewOwn->value);
    }

    public function canViewPendingHolidays(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, HolidayPermission::ViewPending->value);
    }

    public function canViewApprovedHolidays(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, HolidayPermission::ViewApproved->value);
    }

    public function canApproveHoliday(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, HolidayPermission::Approve->value);
    }

    public function canRejectHoliday(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, HolidayPermission::Reject->value);
    }
}
