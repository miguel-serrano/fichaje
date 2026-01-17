<?php

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Application\Command\ClockOutCommand;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class ClockOutCommandHandler
{
    public function __construct(
        private TimeTrackingService $service,
        private UserRepositoryInterface $userRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(ClockOutCommand $command): void
    {
        $user = $this->userRepository->findByUuidOrFail($command->userUuid);

        $this->permissionChecker->assertHasPermission($user, TimeTrackingPermission::ClockOut->value);

        $this->service->clockOut(
            $command->userUuid->value(),
            $command->timeEntryId?->value()
        );
    }
}
