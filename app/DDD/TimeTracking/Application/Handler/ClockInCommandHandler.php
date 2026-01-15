<?php

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Application\Command\ClockInCommand;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class ClockInCommandHandler
{
    public function __construct(
        private TimeTrackingService $service,
        private UserRepositoryInterface $userRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(ClockInCommand $command): void
    {
        $user = $this->userRepository->findByUuidOrFail($command->userUuid);

        $this->permissionChecker->ensureHasPermission($user, TimeTrackingPermission::ClockIn->value);

        $this->service->clockIn($command->userUuid->value());
    }
}
