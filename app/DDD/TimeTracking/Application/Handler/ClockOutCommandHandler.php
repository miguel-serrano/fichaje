<?php

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\TimeTracking\Application\Command\ClockOutCommand;
use App\DDD\TimeTracking\Services\TimeTrackingService;

class ClockOutCommandHandler
{
    public function __construct(
        private TimeTrackingService $service
    ) {}

    public function handle(ClockOutCommand $command): void
    {
        $this->service->clockOut($command->userUuid, $command->timeEntryId);
    }
}
