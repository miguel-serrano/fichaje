<?php

namespace App\DDD\TimeTracking\Application\Command;

class ClockOutCommand
{
    public function __construct(
        public readonly string $userUuid,
        public readonly ?int $timeEntryId = null,
    ) {
    }
}
