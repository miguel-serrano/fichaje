<?php

declare(strict_types=1);

namespace App\DDD\Notification\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Notification\Application\Command\MarkNotificationAsReadCommand;
use App\DDD\Notification\Domain\Interface\NotificationRepositoryInterface;
use App\DDD\Notification\Domain\Permission\NotificationPermission;

class MarkNotificationAsReadCommandHandler
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(MarkNotificationAsReadCommand $command): bool
    {
        $this->authorizationService->denyAccessUnlessGranted(NotificationPermission::MarkRead->value, $command->userId->value());

        return $this->notificationRepository->markAsRead(
            $command->notificationId,
            $command->userId
        );
    }
}
