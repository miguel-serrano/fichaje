<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\User\Application\Query\GetAllUsersQuery;
use App\DDD\User\Application\Response\GetAllUsersQueryResponse;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Permission\UserPermission;

class GetAllUsersQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetAllUsersQuery $query): GetAllUsersQueryResponse
    {
        $this->authorizationService->denyAccessUnlessGranted(UserPermission::View->value, $query->authenticatedUserId->value());

        return new GetAllUsersQueryResponse($this->userRepository->findAll());
    }
}
