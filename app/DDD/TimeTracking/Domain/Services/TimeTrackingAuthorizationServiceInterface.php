<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Services;

use App\DDD\User\Domain\Entity\User;

interface TimeTrackingAuthorizationServiceInterface
{
    public function assertCanClockIn(User $user): void;

    public function assertCanClockOut(User $user): void;

    public function assertCanViewTimeEntry(User $user): void;

    public function assertCanCloseOrphanEntries(User $user): void;

    public function assertCanViewReports(User $user): void;
}
