<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;

class DeleteUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(DeleteUserCommand $command): void
    {
        $targetUser = $this->userRepository->findByIdOrFail($command->targetUserId);

        $authenticatedUser = $this->userRepository->findByIdOrFail($command->authenticatedUserId);

        $this->authorizationService->ensureCanDelete($authenticatedUser, $targetUser);

        $this->userRepository->delete($command->targetUserId);
    }
}
