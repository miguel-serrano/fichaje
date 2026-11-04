<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Voter;

use App\DDD\Authorization\Domain\Voter\AbstractPermissionVoter;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;

final class HolidayVoter extends AbstractPermissionVoter
{
    /**
     * @return string[]
     */
    public function supportedAttributes(): array
    {
        return array_map(
            fn (HolidayPermission $case) => $case->value,
            HolidayPermission::cases()
        );
    }
}
