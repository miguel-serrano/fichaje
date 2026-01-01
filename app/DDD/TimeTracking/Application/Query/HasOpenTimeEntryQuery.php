<?php

namespace App\DDD\TimeTracking\Application\Query;

class HasOpenTimeEntryQuery
{
    public function __construct(
        public readonly string $userUuid
    ) {}
}
