<?php

namespace App\DDD\Authentication\Application\Command;

use App\DDD\Authentication\Domain\ValueObjects\PlainPassword;
use App\DDD\User\Domain\ValueObjects\Email;

final class LoginCommand
{
    private function __construct(
        public readonly Email $email,
        public readonly PlainPassword $password,
    ) {
    }

    public static function create(string $email, string $password): self
    {
        return new self(
            email: Email::make($email),
            password: PlainPassword::make($password),
        );
    }
}
