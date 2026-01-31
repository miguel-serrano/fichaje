<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\TimeTracking\Application\Query\GetDailyHoursHistoryQuery;
use App\DDD\TimeTracking\Application\Response\GetDailyHoursHistoryQueryResponse;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class GetDailyHoursHistoryQueryHandler
{
    public function __construct(
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private UserRepositoryInterface $userRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetDailyHoursHistoryQuery $query): GetDailyHoursHistoryQueryResponse
    {
        $user = $this->userRepository->findByIdOrFail($query->userId);

        $this->authorizationService->denyAccessUnlessGranted(TimeTrackingPermission::ViewOwn->value, $user->id()->value());

        $entries = $this->timeEntryRepository->findByUserIdInDateRange(
            $query->userId,
            $query->days
        );

        return new GetDailyHoursHistoryQueryResponse($entries, $query->days);
    }
}
