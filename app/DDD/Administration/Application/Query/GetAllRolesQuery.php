<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Query;

final class GetAllRolesQuery
{
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }
}
