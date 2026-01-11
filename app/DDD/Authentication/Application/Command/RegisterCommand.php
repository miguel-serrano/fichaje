<?php

namespace App\DDD\Authentication\Application\Command;

final class RegisterCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {
    }
}
