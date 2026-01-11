<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\TimeTracking\Application\Command\CloseOrphanTimeEntriesCommand;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;

final class CloseOrphanTimeEntriesCommandHandler
{
    public function __construct(private TimeTrackingService $service)
    {
    }

    /**
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function handle(CloseOrphanTimeEntriesCommand $command): array
    {
        return $this->service->closeOrphanTimeEntries();
    }
}
