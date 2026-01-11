<?php

namespace App\DDD\User\Application\Query;

class GetAllUsersQuery
{
    public function __construct(
        public int $authenticatedUserId,
    ) {
    }
}
