<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\User\Application\Query\GetUserDailyRegistrosQuery;
use App\DDD\User\Application\Response\GetUserDailyRegistrosQueryResponse;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Permission\UserPermission;

class GetUserDailyRegistrosQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetUserDailyRegistrosQuery $query): GetUserDailyRegistrosQueryResponse
    {
        $this->userRepository->findByIdOrFail($query->targetUserId);

        $this->authorizationService->denyAccessUnlessGranted(UserPermission::View->value, $query->authenticatedUserId->value(), $query->targetUserId->value());

        return new GetUserDailyRegistrosQueryResponse(
            $this->userRepository->findDailyTimeEntriesByUserId($query->targetUserId)
        );
    }
}
