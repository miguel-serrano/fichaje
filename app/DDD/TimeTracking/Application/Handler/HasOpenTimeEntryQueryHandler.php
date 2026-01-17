<?php

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Application\Query\HasOpenTimeEntryQuery;
use App\DDD\TimeTracking\Application\Response\HasOpenTimeEntryQueryResponse;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class HasOpenTimeEntryQueryHandler
{
    public function __construct(
        private TimeTrackingService $service,
        private UserRepositoryInterface $userRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(HasOpenTimeEntryQuery $query): HasOpenTimeEntryQueryResponse
    {
        $user = $this->userRepository->findByUuidOrFail($query->userUuid);

        $this->permissionChecker->assertHasPermission($user, TimeTrackingPermission::ViewOwn->value);

        return new HasOpenTimeEntryQueryResponse(
            $this->service->hasOpenTimeEntry($query->userUuid->value())
        );
    }
}
