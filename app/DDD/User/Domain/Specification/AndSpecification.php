<?php

namespace App\DDD\User\Domain\Specification;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class AndSpecification extends CompositeSpecification
{
    public function __construct(
        private Specification $left,
        private Specification $right,
    ) {
    }

    public function isSatisfiedBy(UserRepositoryInterface $userRepository): bool
    {
        return $this->left->isSatisfiedBy($userRepository) && $this->right->isSatisfiedBy($userRepository);
    }
}
