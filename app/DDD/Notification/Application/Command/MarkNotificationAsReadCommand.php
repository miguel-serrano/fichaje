<?php

namespace App\DDD\Notification\Application\Command;

use App\DDD\Notification\Domain\ValueObjects\NotificationId;
use App\DDD\User\Domain\ValueObjects\UserId;

final class MarkNotificationAsReadCommand
{
    private function __construct(
        public readonly NotificationId $notificationId,
        public readonly UserId $userId,
    ) {
    }

    public static function create(int $notificationId, int $userId): self
    {
        return new self(
            notificationId: NotificationId::make($notificationId),
            userId: UserId::make($userId),
        );
    }
}
