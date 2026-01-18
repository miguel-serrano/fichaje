<?php

namespace App\DDD\User\Domain\Services;

use App\DDD\User\Domain\Exceptions\UserAlreadyExistsException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Email;

class UserExistValidator
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * Validate that a user with the given email does not already exist.
     *
     * @throws UserAlreadyExistsException
     */
    public function validate(Email $email): void
    {
        if ($this->exists($email)) {
            throw new UserAlreadyExistsException($email->value());
        }
    }

    /**
     * Check if a user with the given email already exists.
     */
    public function exists(Email $email): bool
    {
        return $this->userRepository->existsByEmail($email);
    }
}
