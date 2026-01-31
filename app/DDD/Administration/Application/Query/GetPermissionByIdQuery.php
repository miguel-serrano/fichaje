<?php

namespace App\DDD\Administration\Application\Query;

final class GetPermissionByIdQuery
{
    public function __construct(
        public int $permissionId,
    ) {}
}
