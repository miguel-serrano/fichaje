<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;

class DeleteUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(DeleteUserCommand $command): void
    {
        $userId = new UserId($command->getId());
        
        // Verify user exists
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new UserNotFoundException("User {$command->getId()} not found");
        }
        
        // Delete user
        $deleted = $this->userRepository->delete($userId);
        if (!$deleted) {
            throw new \RuntimeException("Failed to delete user {$command->getId()}");
        }
    }
}
