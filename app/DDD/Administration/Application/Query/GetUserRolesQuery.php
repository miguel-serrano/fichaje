<?php

namespace App\DDD\Administration\Application\Query;

final class GetUserRolesQuery
{
    public function __construct(
        public int $userId,
    ) {}
}
