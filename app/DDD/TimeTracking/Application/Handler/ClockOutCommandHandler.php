<?php

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Application\Command\ClockOutCommand;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class ClockOutCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private PermissionCheckerInterface $permissionChecker,
        private TimeTrackingService $service,
    ) {
    }

    public function handle(ClockOutCommand $command): void
    {
        $user = $this->userRepository->findByUuidOrFail($command->userUuid);
        $user->ensureIsActive();

        $this->permissionChecker->assertHasPermission(
            $user,
            TimeTrackingPermission::ClockOut->value
        );

        $this->service->ensureCanClockOut($user);

        $openEntry = $this->service->getOpenEntry($user);
        $openEntry->close();

        $this->timeEntryRepository->update($openEntry);
    }
}
