<?php

namespace App\DDD\User\Domain\Specification;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class NotSpecification extends CompositeSpecification
{
    public function __construct(
        private Specification $specification,
    ) {
    }

    public function isSatisfiedBy(UserRepositoryInterface $userRepository): bool
    {
        return !$this->specification->isSatisfiedBy($userRepository);
    }
}
