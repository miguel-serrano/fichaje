<?php

namespace App\DDD\Authorization\Application\Query;

final class GetRoleByIdQuery
{
    public function __construct(
        public int $roleId,
    ) {
    }
}
