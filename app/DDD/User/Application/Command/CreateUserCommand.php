<?php

namespace App\DDD\User\Application\Command;

class CreateUserCommand
{
    public function __construct(
        private string $email,
        private string $name
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
