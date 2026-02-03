<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Shared\Domain\Event\EventBusInterface;
use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Permission\UserPermission;

class DeleteUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuthorizationServiceInterface $authorizationService,
        private EventBusInterface $eventBus,
    ) {
    }

    public function handle(DeleteUserCommand $command): void
    {
        $user = $this->userRepository->findByIdOrFail($command->targetUserId);

        $this->authorizationService->denyAccessUnlessGranted(UserPermission::Delete->value, $command->authenticatedUserId->value());

        $user->delete($this->userRepository);

        $this->eventBus->publish(...$user->pullDomainEvents());
    }
}
