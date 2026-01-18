<?php

namespace App\DDD\Authentication\Application\Command;

use App\DDD\Authentication\Domain\ValueObjects\PlainPassword;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\Name;

final class RegisterCommand
{
    private function __construct(
        public readonly Name $name,
        public readonly Email $email,
        public readonly PlainPassword $password,
    ) {
    }

    public static function create(string $name, string $email, string $password): self
    {
        return new self(
            name: Name::make($name),
            email: Email::make($email),
            password: PlainPassword::make($password),
        );
    }
}
