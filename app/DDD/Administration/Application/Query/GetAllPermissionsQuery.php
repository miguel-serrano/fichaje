<?php

namespace App\DDD\Administration\Application\Query;

final class GetAllPermissionsQuery
{
    public function __construct(
        public ?string $boundedContext = null,
    ) {}
}
