<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\exceptions\UserNotFoundException;
use App\DDD\User\Domain\exceptions\UserHasNotPermissionsException;
use App\DDD\User\Application\Command\GetUserByIdQuery;
use App\DDD\User\Domain\Entity\User; // Import User entity

class GetUserByIdQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(GetUserByIdQuery $query): User // Changed return type
    {
        $userId = new UserId((int)$query->getId()); // Cast to int
        $user = $this->userRepository->findById($userId);

        $this->validate($user, $query);

        return $user; // Return User entity directly
    }

    private function validate(?User $user, GetUserByIdQuery $query): void // Added ?User type-hint
    {
        if (!$user) {
            throw new UserNotFoundException("User not found");
        }
    }
}

