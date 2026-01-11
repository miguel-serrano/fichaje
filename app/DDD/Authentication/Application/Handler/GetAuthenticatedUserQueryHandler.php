<?php

namespace App\DDD\Authentication\Application\Handler;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authentication\Domain\Exceptions\UserNotAuthenticatedException;
use App\DDD\Authentication\Domain\Services\AuthenticationService;
use App\DDD\User\Domain\Entity\User;

final class GetAuthenticatedUserQueryHandler
{
    public function __construct(
        private AuthenticationService $authService,
    ) {
    }

    public function handle(GetAuthenticatedUserQuery $query): User
    {
        $user = $this->authService->user();

        if (!$user) {
            throw new UserNotAuthenticatedException();
        }

        return $user;
    }
}
