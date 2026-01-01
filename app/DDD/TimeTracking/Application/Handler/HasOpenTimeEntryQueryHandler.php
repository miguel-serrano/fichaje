<?php

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\TimeTracking\Application\Query\HasOpenTimeEntryQuery;
use App\DDD\TimeTracking\Services\TimeTrackingService;

class HasOpenTimeEntryQueryHandler
{
    public function __construct(
        private TimeTrackingService $service
    ) {}

    public function handle(HasOpenTimeEntryQuery $query): bool
    {
        return $this->service->hasOpenTimeEntry($query->userUuid);
    }
}
