<?php

namespace App\DDD\User\Domain\Specification;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;

interface Specification
{
    public function isSatisfiedBy(UserRepositoryInterface $userRepository): bool;

    public function and(Specification $other): Specification;

    public function or(Specification $other): Specification;

    public function not(): Specification;
}
