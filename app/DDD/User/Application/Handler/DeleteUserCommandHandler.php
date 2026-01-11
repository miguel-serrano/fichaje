<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class DeleteUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(DeleteUserCommand $command): void
    {
        $authenticatedUserId = new UserId($command->authenticatedUserId);
        $targetUserId = new UserId($command->targetUserId);

        $authenticatedUser = $this->userRepository->findByIdOrFail($authenticatedUserId);
        $targetUser = $this->userRepository->findByIdOrFail($targetUserId);

        $this->authorizationService->ensureCanDelete($authenticatedUser, $targetUser);

        $deleted = $this->userRepository->delete($targetUserId);
        if (!$deleted) {
            throw new \RuntimeException("Failed to delete user {$command->targetUserId}");
        }
    }
}
