<?php

declare(strict_types=1);

namespace App\DDD\Notification\Application\Handler;

use App\DDD\Notification\Application\Command\MarkNotificationAsReadCommand;
use App\DDD\Notification\Domain\Interface\NotificationRepositoryInterface;
use App\DDD\Notification\Domain\Services\NotificationAuthorizationServiceInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class MarkNotificationAsReadCommandHandler
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
        private UserRepositoryInterface $userRepository,
        private NotificationAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(MarkNotificationAsReadCommand $command): bool
    {
        $user = $this->userRepository->findByIdOrFail($command->userId);

        $this->authorizationService->assertCanMarkAsRead($user);

        return $this->notificationRepository->markAsRead(
            $command->notificationId,
            $command->userId
        );
    }
}
