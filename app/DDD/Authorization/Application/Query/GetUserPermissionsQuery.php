<?php

namespace App\DDD\Authorization\Application\Query;

final class GetUserPermissionsQuery
{
    public function __construct(
        public int $userId,
    ) {
    }
}
