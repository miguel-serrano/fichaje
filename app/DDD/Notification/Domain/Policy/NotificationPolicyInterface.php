<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain\Policy;

use App\DDD\User\Domain\Entity\User;

interface NotificationPolicyInterface
{
    public function canViewOwnNotifications(User $user): bool;

    public function canViewAllNotifications(User $user): bool;

    public function canSendNotification(User $user): bool;

    public function canMarkAsRead(User $user): bool;

    public function canDeleteNotification(User $user): bool;
}
