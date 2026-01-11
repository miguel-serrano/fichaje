<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Query\GetUserByIdQuery;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class GetUserByIdQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetUserByIdQuery $query): User
    {
        $authenticatedUserId = new UserId($query->authenticatedUserId);
        $targetUserId = new UserId($query->targetUserId);

        $authenticatedUser = $this->userRepository->findByIdOrFail($authenticatedUserId);
        $targetUser = $this->userRepository->findByIdOrFail($targetUserId);

        $this->authorizationService->ensureCanView($authenticatedUser, $targetUser);

        return $targetUser;
    }
}
