<?php

namespace App\DDD\User\Application\Query;

final class GetUserByIdQuery
{
    public function __construct(
        public int $authenticatedUserId,
        public int $targetUserId,
    ) {
    }
}
