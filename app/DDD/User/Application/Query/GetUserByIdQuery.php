<?php

namespace App\DDD\User\Application\Query;

use App\Models\User as EloquentUser;

final class GetUserByIdQuery
{
    public function __construct(
        public EloquentUser $authenticatedUser,
        public int $targetUserId
    ) {}
}
