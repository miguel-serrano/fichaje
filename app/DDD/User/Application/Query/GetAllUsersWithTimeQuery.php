<?php

namespace App\DDD\User\Application\Query;

final class GetAllUsersWithTimeQuery
{
    public function __construct(
        public int $authenticatedUserId
    ) {}
}
