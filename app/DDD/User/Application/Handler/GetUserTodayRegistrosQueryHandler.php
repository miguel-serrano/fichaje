<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Query\GetUserTodayRegistrosQuery;
use App\DDD\User\Application\Response\GetUserTodayRegistrosQueryResponse;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;

class GetUserTodayRegistrosQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetUserTodayRegistrosQuery $query): GetUserTodayRegistrosQueryResponse
    {
        $targetUser = $this->userRepository->findByIdOrFail($query->targetUserId);

        $authenticatedUser = $this->userRepository->findByIdOrFail($query->authenticatedUserId);

        $this->authorizationService->ensureCanView($authenticatedUser, $targetUser);

        return new GetUserTodayRegistrosQueryResponse(
            $this->userRepository->findTodayTimeEntriesByUserId($query->targetUserId)
        );
    }
}
