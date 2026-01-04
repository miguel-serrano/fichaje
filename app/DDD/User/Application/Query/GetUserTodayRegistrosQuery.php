<?php

namespace App\DDD\User\Application\Query;

class GetUserTodayRegistrosQuery
{
    public function __construct(
        private int $userId
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }
}
