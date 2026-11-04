<?php

declare(strict_types=1);

namespace App\DDD\Administration\Domain\Voter;

use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Authorization\Domain\Voter\AbstractPermissionVoter;

final class AdministrationVoter extends AbstractPermissionVoter
{
    /**
     * @return string[]
     */
    public function supportedAttributes(): array
    {
        return array_map(
            fn (AdministrationPermission $case) => $case->value,
            AdministrationPermission::cases()
        );
    }
}
