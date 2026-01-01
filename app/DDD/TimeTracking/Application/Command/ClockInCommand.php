<?php

namespace App\DDD\TimeTracking\Application\Command;

class ClockInCommand
{
    public function __construct(
        public readonly string $userUuid
    ) {}
}
