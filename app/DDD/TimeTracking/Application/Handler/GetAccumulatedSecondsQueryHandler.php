<?php

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\TimeTracking\Application\Query\GetAccumulatedSecondsQuery;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;

class GetAccumulatedSecondsQueryHandler
{
    public function __construct(
        private TimeTrackingService $service
    ) {}

    public function handle(GetAccumulatedSecondsQuery $query): int
    {
        return $this->service->getAccumulatedSeconds($query->userUuid);
    }
}
