<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Query;

final class GetUserHolidaysQuery
{
    public function __construct(
        public int $userId
    ) {}
}
