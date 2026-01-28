<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain\Services;

use App\DDD\User\Domain\Entity\User;

interface NotificationAuthorizationServiceInterface
{
    public function assertCanViewOwnNotifications(User $user): void;

    public function assertCanViewAllNotifications(User $user): void;

    public function assertCanSendNotification(User $user): void;

    public function assertCanMarkAsRead(User $user): void;

    public function assertCanDeleteNotification(User $user): void;
}
