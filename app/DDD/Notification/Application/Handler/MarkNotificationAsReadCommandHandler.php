<?php

namespace App\DDD\Notification\Application\Handler;

use App\DDD\Notification\Application\Command\MarkNotificationAsReadCommand;
use App\DDD\Notification\Domain\Interface\NotificationRepositoryInterface;

class MarkNotificationAsReadCommandHandler
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
    ) {
    }

    public function handle(MarkNotificationAsReadCommand $command): bool
    {
        return $this->notificationRepository->markAsRead(
            $command->notificationId,
            $command->userId
        );
    }
}
