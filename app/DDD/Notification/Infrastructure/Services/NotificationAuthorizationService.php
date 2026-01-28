<?php

declare(strict_types=1);

namespace App\DDD\Notification\Infrastructure\Services;

use App\DDD\Notification\Domain\Exceptions\UnauthorizedNotificationException;
use App\DDD\Notification\Domain\Policy\NotificationPolicyInterface;
use App\DDD\Notification\Domain\Services\NotificationAuthorizationServiceInterface;
use App\DDD\User\Domain\Entity\User;

final class NotificationAuthorizationService implements NotificationAuthorizationServiceInterface
{
    public function __construct(
        private readonly NotificationPolicyInterface $policy,
    ) {
    }

    public function assertCanViewOwnNotifications(User $user): void
    {
        if (!$this->policy->canViewOwnNotifications($user)) {
            throw UnauthorizedNotificationException::forViewOwn();
        }
    }

    public function assertCanViewAllNotifications(User $user): void
    {
        if (!$this->policy->canViewAllNotifications($user)) {
            throw UnauthorizedNotificationException::forViewAll();
        }
    }

    public function assertCanSendNotification(User $user): void
    {
        if (!$this->policy->canSendNotification($user)) {
            throw UnauthorizedNotificationException::forSend();
        }
    }

    public function assertCanMarkAsRead(User $user): void
    {
        if (!$this->policy->canMarkAsRead($user)) {
            throw UnauthorizedNotificationException::forMarkAsRead();
        }
    }

    public function assertCanDeleteNotification(User $user): void
    {
        if (!$this->policy->canDeleteNotification($user)) {
            throw UnauthorizedNotificationException::forDelete();
        }
    }
}
