<?php

namespace App\DDD\Administration\Application\Query;

final class GetUserPermissionsQuery
{
    public function __construct(
        public int $userId,
    ) {}
}
