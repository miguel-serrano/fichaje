<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Shared\Domain\Event\EventBusInterface;
use App\DDD\User\Application\Command\ToggleUserActiveCommand;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Permission\UserPermission;

class ToggleUserActiveCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuthorizationServiceInterface $authorizationService,
        private EventBusInterface $eventBus,
    ) {
    }

    public function handle(ToggleUserActiveCommand $command): bool
    {
        $this->authorizationService->denyAccessUnlessGranted(UserPermission::ToggleActive->value, $command->authenticatedUserId->value());

        $user = $this->userRepository->findByIdOrFail($command->targetUserId);

        $newState = $user->toggleActive();

        $this->userRepository->save($user);

        $this->eventBus->publish(...$user->pullDomainEvents());

        return $newState;
    }
}
