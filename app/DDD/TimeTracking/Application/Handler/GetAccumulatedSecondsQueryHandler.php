<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\TimeTracking\Application\Query\GetAccumulatedSecondsQuery;
use App\DDD\TimeTracking\Application\Response\GetAccumulatedSecondsQueryResponse;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Services\TimeTrackingAuthorizationServiceInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class GetAccumulatedSecondsQueryHandler
{
    public function __construct(
        private TimeTrackingService $service,
        private UserRepositoryInterface $userRepository,
        private TimeTrackingAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetAccumulatedSecondsQuery $query): GetAccumulatedSecondsQueryResponse
    {
        $user = $this->userRepository->findByUuidOrFail($query->userUuid);

        $this->authorizationService->assertCanViewTimeEntry($user);

        return new GetAccumulatedSecondsQueryResponse(
            $this->service->getAccumulatedSeconds($query->userUuid->value())
        );
    }
}
