<?php

namespace App\DDD\User\Application;

use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\UserRepositoryInterface;
use App\DDD\User\Domain\exceptions\UserNotFoundException;

class DeleteUserUseCase
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function execute(string $id): void
    {
        $userId = new UserId($id);
        
        // Verify user exists
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new UserNotFoundException("User {$id} not found");
        }
        
        // Delete user
        $deleted = $this->userRepository->delete($userId);
        if (!$deleted) {
            throw new \RuntimeException("Failed to delete user {$id}");
        }
    }
}

