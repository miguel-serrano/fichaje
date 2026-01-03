<?php

namespace App\DDD\User\Application\Query;

class GetUserDailyRegistrosQuery
{
    public function __construct(
        private int $userId
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }
}
