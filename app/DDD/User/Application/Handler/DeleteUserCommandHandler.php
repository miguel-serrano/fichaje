<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\User as EloquentUser;

class DeleteUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationServiceInterface $authorizationService
    ) {}

    public function handle(DeleteUserCommand $command): void
    {
        $userId = new UserId($command->targetUserId);

        $user = $this->userRepository->findById($userId);
        if (! $user) {
            throw new UserNotFoundException("User {$command->targetUserId} not found");
        }

        // Get Eloquent user for authorization
        $targetEloquentUser = EloquentUser::query()->find($command->targetUserId);

        // Authorization check (replaces the fragile remember_token check)
        $this->authorizationService->ensureCanDelete(
            $command->authenticatedUser,
            $targetEloquentUser
        );

        $deleted = $this->userRepository->delete($userId);
        if (! $deleted) {
            throw new \RuntimeException("Failed to delete user {$command->targetUserId}");
        }
    }
}
