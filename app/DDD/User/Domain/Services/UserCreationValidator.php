<?php

namespace App\DDD\User\Domain\Services;

use App\DDD\User\Domain\Exceptions\DailyUserRegistrationLimitExceededException;
use App\DDD\User\Domain\Exceptions\MaxUsersLimitExceededException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Specification\DailyLimitSpecification;
use App\DDD\User\Domain\Specification\LimitSpecification;

class UserCreationValidator
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private int $maxUsersLimit,
        private int $daylimit,
    ) {
    }

    /**
     * Validate if a new user can be created based on business rules.
     *
     * @throws MaxUsersLimitExceededException
     * @throws DailyUserRegistrationLimitExceededException
     */
    public function isSatisfiedBy(): void
    {
        $limit = new LimitSpecification($this->maxUsersLimit);
        $daylimit = new DailyLimitSpecification($this->daylimit);

        if (!$limit->and($daylimit)->isSatisfiedBy($this->userRepository)) {
            throw new MaxUsersLimitExceededException();
        }
    }
}
