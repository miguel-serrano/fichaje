<?php

namespace App\DDD\User\Domain\Services;

use App\DDD\User\Domain\Exceptions\MaxUsersLimitExceededException;
use App\DDD\User\Domain\Exceptions\UserAlreadyExistsException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Email;

class UserCreationPolicyService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private int $maxUsersLimit,
        private int $dailyUsersLimit,
    ) {
    }

    /**
     * Validates all policies for user creation.
     *
     * @throws UserAlreadyExistsException
     * @throws MaxUsersLimitExceededException
     */
    public function canCreateUser(Email $email): void
    {
        $this->ensureEmailIsUnique($email);
        $this->ensureMaxUsersNotExceeded();
        $this->ensureDailyLimitNotExceeded();
    }

    private function ensureEmailIsUnique(Email $email): void
    {
        if ($this->userRepository->existsByEmail($email)) {
            throw new UserAlreadyExistsException($email->value());
        }
    }

    private function ensureMaxUsersNotExceeded(): void
    {
        $totalUsers = $this->userRepository->count();

        if ($totalUsers >= $this->maxUsersLimit) {
            throw new MaxUsersLimitExceededException("Maximum users limit of {$this->maxUsersLimit} exceeded");
        }
    }

    private function ensureDailyLimitNotExceeded(): void
    {
        $usersCreatedToday = $this->userRepository->countTodayRegistrations();

        if ($usersCreatedToday >= $this->dailyUsersLimit) {
            throw new MaxUsersLimitExceededException("Daily users limit of {$this->dailyUsersLimit} exceeded");
        }
    }
}
