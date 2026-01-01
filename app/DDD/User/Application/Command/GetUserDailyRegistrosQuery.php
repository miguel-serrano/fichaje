<?php

namespace App\DDD\User\Application\Command;

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
