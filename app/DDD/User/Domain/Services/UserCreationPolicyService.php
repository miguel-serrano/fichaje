<?php

namespace App\DDD\User\Domain\Services;

use App\DDD\User\Domain\Exceptions\MaxUsersLimitExceededException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

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
     * @throws MaxUsersLimitExceededException
     * @throws DailyUserRegistrationLimitExceededException
     */
    public function canCreateUser(): void
    {
        $this->ensureMaxUsersNotExceeded();
        $this->ensureDailyLimitNotExceeded();
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
