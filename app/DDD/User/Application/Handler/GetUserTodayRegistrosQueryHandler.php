<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\User\Application\Query\GetUserTodayRegistrosQuery;
use App\DDD\User\Application\Response\GetUserTodayRegistrosQueryResponse;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Permission\UserPermission;

class GetUserTodayRegistrosQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetUserTodayRegistrosQuery $query): GetUserTodayRegistrosQueryResponse
    {
        $this->userRepository->findByIdOrFail($query->targetUserId);

        $this->authorizationService->denyAccessUnlessGranted(UserPermission::View->value, $query->authenticatedUserId->value(), $query->targetUserId->value());

        return new GetUserTodayRegistrosQueryResponse(
            $this->userRepository->findTodayTimeEntriesByUserId($query->targetUserId)
        );
    }
}
