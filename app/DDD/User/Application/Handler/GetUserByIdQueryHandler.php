<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Query\GetUserByIdQuery;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;

class GetUserByIdQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetUserByIdQuery $query): User
    {
        $targetUser = $this->userRepository->findByIdOrFail($query->targetUserId);

        $authenticatedUser = $this->userRepository->findByIdOrFail($query->authenticatedUserId);

        $this->authorizationService->assertCanView($authenticatedUser, $targetUser);

        return $targetUser;
    }
}
