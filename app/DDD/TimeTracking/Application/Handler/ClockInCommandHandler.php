<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Shared\Domain\Event\EventBusInterface;
use App\DDD\TimeTracking\Application\Command\ClockInCommand;
use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
use App\DDD\TimeTracking\Domain\Interface\ClockInValidatorInterface;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\DDD\User\Domain\Interface\ActiveUserRepositoryInterface;

class ClockInCommandHandler
{
    public function __construct(
        private ActiveUserRepositoryInterface $userRepository,
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private AuthorizationServiceInterface $authorizationService,
        private ClockInValidatorInterface $clockInValidator,
        private EventBusInterface $eventBus,
    ) {
    }

    public function handle(ClockInCommand $command): void
    {
        $user = $this->userRepository->findActiveByUuidOrFail($command->userUuid);

        $this->authorizationService->denyAccessUnlessGranted(TimeTrackingPermission::ClockIn->value, $user->id()->value());

        $user->ensureCanClockIn($this->clockInValidator);

        $timeEntry = TimeEntry::create($user->id());

        $this->timeEntryRepository->save($timeEntry);

        $this->eventBus->publish(...$timeEntry->pullDomainEvents());
    }
}
