<?php

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Application\Query\HasOpenTimeEntryQuery;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Uuid;

class HasOpenTimeEntryQueryHandler
{
    public function __construct(
        private TimeTrackingService $service,
        private UserRepositoryInterface $userRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(HasOpenTimeEntryQuery $query): bool
    {
        $user = $this->userRepository->findByUuidOrFail(new Uuid($query->userUuid));
        $this->permissionChecker->ensureHasPermission($user, TimeTrackingPermission::ViewOwn->value);

        return $this->service->hasOpenTimeEntry($query->userUuid);
    }
}
