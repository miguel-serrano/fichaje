<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain\Voter;

use App\DDD\Authorization\Infrastructure\Service\Voter\AbstractPermissionVoter;
use App\DDD\Notification\Domain\Permission\NotificationPermission;

final class NotificationVoter extends AbstractPermissionVoter
{
    /**
     * @return string[]
     */
    public function supportedAttributes(): array
    {
        return array_map(
            fn (NotificationPermission $case) => $case->value,
            NotificationPermission::cases()
        );
    }
}
