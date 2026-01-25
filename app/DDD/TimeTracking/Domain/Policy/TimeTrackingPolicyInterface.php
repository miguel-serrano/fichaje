<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Policy;

use App\DDD\User\Domain\Entity\User;

interface TimeTrackingPolicyInterface
{
    public function canClockIn(User $user): bool;

    public function canClockOut(User $user): bool;

    public function canViewTimeEntry(User $user): bool;

    public function canCloseOrphanEntries(User $user): bool;

    public function canViewReports(User $user): bool;
}
