<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Voter;

use App\DDD\Authorization\Domain\Voter\AbstractPermissionVoter;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;

final class TimeTrackingVoter extends AbstractPermissionVoter
{
    /**
     * @return string[]
     */
    public function supportedAttributes(): array
    {
        return array_map(
            fn (TimeTrackingPermission $case) => $case->value,
            TimeTrackingPermission::cases()
        );
    }
}
