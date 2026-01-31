<?php

namespace App\DDD\Administration\Application\Query;

final class GetRoleByIdQuery
{
    public function __construct(
        public int $roleId,
    ) {}
}
