<?php

namespace App\DDD\Authorization\Application\Query;

final class GetUserRolesQuery
{
    public function __construct(
        public int $userId,
    ) {
    }
}
