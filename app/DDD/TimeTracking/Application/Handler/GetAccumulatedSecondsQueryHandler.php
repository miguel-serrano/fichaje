<?php

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Application\Query\GetAccumulatedSecondsQuery;
use App\DDD\TimeTracking\Application\Response\GetAccumulatedSecondsQueryResponse;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class GetAccumulatedSecondsQueryHandler
{
    public function __construct(
        private TimeTrackingService $service,
        private UserRepositoryInterface $userRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(GetAccumulatedSecondsQuery $query): GetAccumulatedSecondsQueryResponse
    {
        $user = $this->userRepository->findByUuidOrFail($query->userUuid);

        $this->permissionChecker->assertHasPermission($user, TimeTrackingPermission::ViewOwn->value);

        return new GetAccumulatedSecondsQueryResponse(
            $this->service->getAccumulatedSeconds($query->userUuid->value())
        );
    }
}
