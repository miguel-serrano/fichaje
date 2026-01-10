<?php

namespace App\DDD\Authorization\Application\Query;

final class GetAllPermissionsQuery
{
    public function __construct(
        public ?string $boundedContext = null,
    ) {
    }
}
