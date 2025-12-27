<?php

namespace App\DDD\User\Application\Command;

class GetUserDailyRegistrosQuery
{
    public function __construct(
        private string $userId
    ) {}

    public function getUserId(): string
    {
        return $this->userId;
    }
}
