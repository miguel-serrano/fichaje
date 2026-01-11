<?php

namespace App\DDD\Authentication\Application\Handler;

use App\DDD\Authentication\Application\Command\LogoutCommand;
use App\DDD\Authentication\Domain\Services\AuthenticationService;

final class LogoutCommandHandler
{
    public function __construct(
        private AuthenticationService $authService,
    ) {
    }

    public function handle(LogoutCommand $command): void
    {
        $this->authService->logout();
    }
}
