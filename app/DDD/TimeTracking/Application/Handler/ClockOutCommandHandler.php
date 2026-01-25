<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\TimeTracking\Application\Command\ClockOutCommand;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Services\TimeTrackingAuthorizationServiceInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class ClockOutCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private TimeTrackingAuthorizationServiceInterface $authorizationService,
        private TimeTrackingService $service,
    ) {
    }

    public function handle(ClockOutCommand $command): void
    {
        $user = $this->userRepository->findByUuidOrFail($command->userUuid);

        $user->ensureIsActive();

        $this->authorizationService->assertCanClockOut($user);

        $this->service->ensureCanClockOut($user);

        $openEntry = $this->service->getOpenEntry($user);

        $openEntry->close();

        $this->timeEntryRepository->update($openEntry);
    }
}
