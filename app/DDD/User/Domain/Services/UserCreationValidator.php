<?php

namespace App\DDD\User\Domain\Services;

use App\DDD\User\Domain\Exceptions\DailyUserRegistrationLimitExceededException;
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
     * @throws DailyUserRegistrationLimitExceededException
     */
    public function validate(): void
    {
        $currentUserCount = $this->userRepository->count();

        if ($currentUserCount >= $this->maxUsersLimit) {
            throw new MaxUsersLimitExceededException($this->maxUsersLimit, $currentUserCount);
        }

        $todayRegistrations = $this->userRepository->countTodayRegistrations();

        if ($todayRegistrations >= DailyUserRegistrationLimitExceededException::MAX_DAILY_REGISTRATIONS) {
            throw new DailyUserRegistrationLimitExceededException($todayRegistrations);
        }
    }

    /**
     * Check if user creation is allowed without throwing exception.
     */
    public function canCreateUser(): bool
    {
        $currentUserCount = $this->userRepository->count();
        $todayRegistrations = $this->userRepository->countTodayRegistrations();

        return $currentUserCount < $this->maxUsersLimit
            && $todayRegistrations < DailyUserRegistrationLimitExceededException::MAX_DAILY_REGISTRATIONS;
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
