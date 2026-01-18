<?php

namespace App\DDD\User\Domain\Specification;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class DailyLimitSpecification extends CompositeSpecification
{
    public function __construct(
        private int $dailyLimit,
    ) {
    }

    public function isSatisfiedBy(UserRepositoryInterface $userRepository): bool
    {
        $todayRegistrations = $userRepository->countTodayRegistrations();

        return $todayRegistrations < $this->dailyLimit;
    }
}
