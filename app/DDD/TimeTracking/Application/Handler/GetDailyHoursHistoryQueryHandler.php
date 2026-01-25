<?php

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
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
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(GetDailyHoursHistoryQuery $query): GetDailyHoursHistoryQueryResponse
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail($query->authenticatedUserId);

        $this->permissionChecker->assertHasPermission($authenticatedUser, TimeTrackingPermission::ViewOwn->value);

        $entries = $this->timeEntryRepository->findByUserIdInDateRange(
            $query->targetUserId,
            $query->days
        );

        return new GetDailyHoursHistoryQueryResponse($entries, $query->days);
    }
}
