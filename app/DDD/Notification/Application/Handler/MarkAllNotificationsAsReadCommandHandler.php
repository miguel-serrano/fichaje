<?php

namespace App\DDD\Notification\Application\Handler;

use App\DDD\Notification\Application\Command\MarkAllNotificationsAsReadCommand;
use App\DDD\Notification\Domain\Interface\NotificationRepositoryInterface;

class MarkAllNotificationsAsReadCommandHandler
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
    ) {
    }

    public function handle(MarkAllNotificationsAsReadCommand $command): int
    {
        return $this->notificationRepository->markAllAsRead($command->userId);
    }
}
