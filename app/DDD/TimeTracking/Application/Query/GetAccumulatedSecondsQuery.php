<?php

namespace App\DDD\TimeTracking\Application\Query;

class GetAccumulatedSecondsQuery
{
    public function __construct(
        public readonly string $userUuid,
    ) {
    }
}
