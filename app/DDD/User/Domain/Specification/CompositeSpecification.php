<?php

namespace App\DDD\User\Domain\Specification;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;

abstract class CompositeSpecification implements Specification
{
    abstract public function isSatisfiedBy(UserRepositoryInterface $userRepository): bool;

    public function and(Specification $other): Specification
    {
        return new AndSpecification($this, $other);
    }

    public function or(Specification $other): Specification
    {
        return new OrSpecification($this, $other);
    }

    public function not(): Specification
    {
        return new NotSpecification($this);
    }
}
