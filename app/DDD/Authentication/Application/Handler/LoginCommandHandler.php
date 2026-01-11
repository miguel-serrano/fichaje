<?php

namespace App\DDD\Authentication\Application\Handler;

use App\DDD\Authentication\Application\Command\LoginCommand;
use App\DDD\Authentication\Domain\Exceptions\InvalidCredentialsException;
use App\DDD\Authentication\Domain\Services\AuthenticationService;
use App\DDD\User\Domain\ValueObjects\Email;

final class LoginCommandHandler
{
    public function __construct(
        private AuthenticationService $authService,
    ) {
    }

    public function handle(LoginCommand $command): void
    {
        $email = new Email($command->email);

        if (!$this->authService->attempt($email, $command->password)) {
            throw new InvalidCredentialsException();
        }
    }
}
