<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Query;

final class GetPendingHolidaysQuery
{
    public function __construct(
        public int $authenticatedUserId,
    ) {
    }
}
