<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Policy;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\DDD\User\Domain\Entity\User;

final class TimeTrackingPolicy implements TimeTrackingPolicyInterface
{
    public function __construct(
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function canClockIn(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, TimeTrackingPermission::ClockIn->value);
    }

    public function canClockOut(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, TimeTrackingPermission::ClockOut->value);
    }

    public function canViewTimeEntry(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, TimeTrackingPermission::ViewOwn->value);
    }

    public function canCloseOrphanEntries(User $user): bool
    {
        return $this->permissionChecker->isSuperAdmin($user);
    }

    public function canViewReports(User $user): bool
    {
        return $this->permissionChecker->isSuperAdmin($user)
            || $this->permissionChecker->hasPermission($user, TimeTrackingPermission::Reports->value);
    }
}
