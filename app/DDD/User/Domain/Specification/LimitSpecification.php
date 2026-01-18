<?php

namespace App\DDD\User\Domain\Specification;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class LimitSpecification extends CompositeSpecification
{
    public function __construct(
        private int $maxUsersLimit,
    ) {
    }

    public function isSatisfiedBy(UserRepositoryInterface $userRepository): bool
    {
        return $userRepository->count() < $this->maxUsersLimit;
    }
}
