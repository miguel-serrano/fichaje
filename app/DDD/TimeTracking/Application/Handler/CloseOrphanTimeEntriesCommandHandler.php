<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\TimeTracking\Application\Command\CloseOrphanTimeEntriesCommand;
use App\DDD\TimeTracking\Application\Response\CloseOrphanTimeEntriesCommandResponse;
use App\DDD\TimeTracking\Application\Service\TimeTrackingNotifierService;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;

final class CloseOrphanTimeEntriesCommandHandler
{
    public function __construct(
        private TimeTrackingService $service,
        private TimeTrackingNotifierService $notifierService,
    ) {}

    public function handle(CloseOrphanTimeEntriesCommand $command): CloseOrphanTimeEntriesCommandResponse
    {
        $closedByUser = $this->service->closeOrphanTimeEntries();
        $this->notifierService->notifyOrphanEntriesClosed($closedByUser);

        return new CloseOrphanTimeEntriesCommandResponse($closedByUser);
    }
}
