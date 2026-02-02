<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\TimeTracking\Application\Command\ClockOutCommand;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Interface\ClockOutValidatorInterface;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\DDD\User\Domain\Interface\ActiveUserRepositoryInterface;

class ClockOutCommandHandler
{
    public function __construct(
        private ActiveUserRepositoryInterface $userRepository,
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private AuthorizationServiceInterface $authorizationService,
        private ClockOutValidatorInterface $clockOutValidator,
        private TimeTrackingService $service,
    ) {
    }

    public function handle(ClockOutCommand $command): void
    {
        $user = $this->userRepository->findActiveByUuidOrFail($command->userUuid);

        $this->authorizationService->denyAccessUnlessGranted(TimeTrackingPermission::ClockOut->value, $user->id()->value());

        $user->ensureCanClockOut($this->clockOutValidator);

        $openEntry = $this->service->getOpenEntry($user);

        $openEntry->close();

        $this->timeEntryRepository->update($openEntry);
    }
}
