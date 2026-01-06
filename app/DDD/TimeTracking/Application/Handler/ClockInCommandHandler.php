<?php

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\TimeTracking\Application\Command\ClockInCommand;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;

class ClockInCommandHandler
{
    public function __construct(
        private TimeTrackingService $service
    ) {}

    public function handle(ClockInCommand $command): void
    {
        $this->service->clockIn($command->userUuid);
    }
}
