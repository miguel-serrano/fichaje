<?php

namespace App\DDD\Authorization\Application\Query;

final class GetPermissionByIdQuery
{
    public function __construct(
        public int $permissionId,
    ) {
    }
}
