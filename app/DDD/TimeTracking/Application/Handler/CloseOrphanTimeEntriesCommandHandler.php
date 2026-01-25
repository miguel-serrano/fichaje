<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\TimeTracking\Application\Command\CloseOrphanTimeEntriesCommand;
use App\DDD\TimeTracking\Application\Response\CloseOrphanTimeEntriesCommandResponse;
use App\DDD\TimeTracking\Application\Service\TimeTrackingNotifierService;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Services\TimeTrackingAuthorizationServiceInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

final class CloseOrphanTimeEntriesCommandHandler
{
    public function __construct(
        private TimeTrackingService $service,
        private TimeTrackingNotifierService $notifierService,
        private UserRepositoryInterface $userRepository,
        private TimeTrackingAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(CloseOrphanTimeEntriesCommand $command): CloseOrphanTimeEntriesCommandResponse
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail($command->authenticatedUserId);

        $this->authorizationService->assertCanCloseOrphanEntries($authenticatedUser);

        $closedByUser = $this->service->closeOrphanTimeEntries();

        $this->notifierService->notifyOrphanEntriesClosed($closedByUser);

        return new CloseOrphanTimeEntriesCommandResponse($closedByUser);
    }
}
