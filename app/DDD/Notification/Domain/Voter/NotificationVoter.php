<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain\Voter;

use App\DDD\Authorization\Domain\Interface\UserPermissionsCheckerInterface;
use App\DDD\Authorization\Infrastructure\Service\Voter\AbstractVoter;
use App\DDD\Notification\Domain\Permission\NotificationPermission;

final class NotificationVoter extends AbstractVoter
{
    public function __construct(
        private readonly UserPermissionsCheckerInterface $permissionsChecker,
    ) {
    }

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

    protected function voteOnAttribute(int $userId, string $attribute, mixed $subject): bool
    {
        if ($this->permissionsChecker->isSuperAdmin($userId)) {
            return true;
        }

        return $this->permissionsChecker->hasPermission($userId, $attribute);
    }
}
