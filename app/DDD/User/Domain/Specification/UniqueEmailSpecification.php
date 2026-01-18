<?php

namespace App\DDD\User\Domain\Specification;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Email;

class UniqueEmailSpecification
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function isSatisfiedBy(Email $email): bool
    {
        return !$this->userRepository->existsByEmail($email);
    }
}
