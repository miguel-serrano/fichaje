<?php

namespace App\DDD\User\Domain\Services;

use App\DDD\User\Domain\Exceptions\MaxUsersLimitExceededException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class UserCreationValidator
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private int $maxUsersLimit
    ) {}

    /**
     * Validate if a new user can be created based on business rules.
     *
     * @throws MaxUsersLimitExceededException
     */
    public function validate(): void
    {
        $currentUserCount = $this->userRepository->count();

        if ($currentUserCount >= $this->maxUsersLimit) {
            throw new MaxUsersLimitExceededException($this->maxUsersLimit, $currentUserCount);
        }
    }

    /**
     * Check if user creation is allowed without throwing exception.
     */
    public function canCreateUser(): bool
    {
        $currentUserCount = $this->userRepository->count();

        return $currentUserCount < $this->maxUsersLimit;
    }

    /**
     * Get the current user count.
     */
    public function getCurrentUserCount(): int
    {
        return $this->userRepository->count();
    }

    /**
     * Get the maximum users limit.
     */
    public function getMaxUsersLimit(): int
    {
        return $this->maxUsersLimit;
    }
}
