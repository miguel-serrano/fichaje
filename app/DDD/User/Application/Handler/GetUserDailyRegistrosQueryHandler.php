<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Query\GetUserDailyRegistrosQuery;
use App\DDD\User\Application\Response\GetUserDailyRegistrosQueryResponse;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;

class GetUserDailyRegistrosQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetUserDailyRegistrosQuery $query): GetUserDailyRegistrosQueryResponse
    {
        $targetUser = $this->userRepository->findByIdOrFail($query->targetUserId);

        $authenticatedUser = $this->userRepository->findByIdOrFail($query->authenticatedUserId);

        $this->authorizationService->ensureCanView($authenticatedUser, $targetUser);

        return new GetUserDailyRegistrosQueryResponse(
            $this->userRepository->findDailyTimeEntriesByUserId($query->targetUserId)
        );
    }
}
