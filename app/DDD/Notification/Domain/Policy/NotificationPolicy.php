<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain\Policy;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Notification\Domain\Permission\NotificationPermission;
use App\DDD\User\Domain\Entity\User;

final class NotificationPolicy implements NotificationPolicyInterface
{
    public function __construct(
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function canViewOwnNotifications(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, NotificationPermission::ViewOwn->value);
    }

    public function canViewAllNotifications(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, NotificationPermission::ViewAll->value);
    }

    public function canSendNotification(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, NotificationPermission::Send->value);
    }

    public function canMarkAsRead(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, NotificationPermission::MarkRead->value);
    }

    public function canDeleteNotification(User $user): bool
    {
        return $this->permissionChecker->hasPermission($user, NotificationPermission::Delete->value);
    }
}
