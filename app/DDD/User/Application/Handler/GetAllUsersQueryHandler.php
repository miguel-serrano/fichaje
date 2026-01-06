<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Query\GetAllUsersQuery;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class GetAllUsersQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationServiceInterface $authorizationService
    ) {}

    public function handle(GetAllUsersQuery $query): array
    {
        $authenticatedUserId = new UserId($query->authenticatedUserId);
        $authenticatedUser = $this->userRepository->findByIdOrFail($authenticatedUserId);

        $this->authorizationService->ensureCanList($authenticatedUser);

        $users = $this->userRepository->findAll();

        return array_map(fn ($user) => $user->toArray(), $users);
    }
}
