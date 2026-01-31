<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Permission\UserPermission;

class DeleteUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(DeleteUserCommand $command): void
    {
        $this->userRepository->findByIdOrFail($command->targetUserId);

        $this->authorizationService->denyAccessUnlessGranted(UserPermission::Delete->value, $command->authenticatedUserId->value(), $command->targetUserId->value());

        $this->userRepository->delete($command->targetUserId);
    }
}
