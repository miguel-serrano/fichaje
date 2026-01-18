<?php

namespace App\DDD\Authentication\Application\Handler;

use App\DDD\Authentication\Application\Command\LoginCommand;
use App\DDD\Authentication\Domain\Exceptions\InvalidCredentialsException;
use App\DDD\Authentication\Domain\Services\AuthenticationService;

final class LoginCommandHandler
{
    public function __construct(
        private AuthenticationService $authService,
    ) {
    }

    public function handle(LoginCommand $command): void
    {
        if (!$this->authService->attempt($command->email, $command->password)) {
            throw new InvalidCredentialsException();
        }
    }
}
