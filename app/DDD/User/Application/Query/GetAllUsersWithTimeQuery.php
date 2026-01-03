<?php

namespace App\DDD\User\Application\Query;

use App\Models\User as EloquentUser;

final class GetAllUsersWithTimeQuery
{
    public function __construct(
        public EloquentUser $authenticatedUser
    ) {}
}
