<?php

namespace App\DDD\Authentication\Application\Command;

final class LoginCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {
    }
}
