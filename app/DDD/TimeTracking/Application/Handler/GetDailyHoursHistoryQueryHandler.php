<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\TimeTracking\Application\Query\GetDailyHoursHistoryQuery;
use App\DDD\TimeTracking\Application\Response\GetDailyHoursHistoryQueryResponse;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Services\TimeTrackingAuthorizationServiceInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class GetDailyHoursHistoryQueryHandler
{
    public function __construct(
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private UserRepositoryInterface $userRepository,
        private TimeTrackingAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetDailyHoursHistoryQuery $query): GetDailyHoursHistoryQueryResponse
    {
        $user = $this->userRepository->findByIdOrFail($query->userId);

        $this->authorizationService->assertCanViewTimeEntry($user);

        $entries = $this->timeEntryRepository->findByUserIdInDateRange(
            $query->userId,
            $query->days
        );

        return new GetDailyHoursHistoryQueryResponse($entries, $query->days);
    }
}
