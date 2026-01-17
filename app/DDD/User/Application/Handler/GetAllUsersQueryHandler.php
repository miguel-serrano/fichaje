<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Query\GetAllUsersQuery;
use App\DDD\User\Application\Response\GetAllUsersQueryResponse;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;

class GetAllUsersQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetAllUsersQuery $query): GetAllUsersQueryResponse
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail($query->authenticatedUserId);

        $this->authorizationService->assertCanList($authenticatedUser);

        return new GetAllUsersQueryResponse($this->userRepository->findAll());
    }
}
