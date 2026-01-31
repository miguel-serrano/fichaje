<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\TimeTracking\Application\Command\ClockInCommand;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class ClockInCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private AuthorizationServiceInterface $authorizationService,
        private TimeTrackingService $service,
    ) {
    }

    public function handle(ClockInCommand $command): void
    {
        $user = $this->userRepository->findByUuidOrFail($command->userUuid);

        $user->ensureIsActive();

        $this->authorizationService->denyAccessUnlessGranted(TimeTrackingPermission::ClockIn->value, $user->id()->value());

        $this->service->ensureCanClockIn($user);

        $timeEntry = TimeEntry::create($user->id());

        $this->timeEntryRepository->save($timeEntry);
    }
}
