<?php

namespace App\DDD\User\Domain\Exceptions;

class MaxUsersLimitExceededException extends \Exception
{
    public function __construct(int $maxUsers, int $currentCount)
    {
        parent::__construct(
            "Cannot create more users. Maximum limit of {$maxUsers} users has been reached. Current count: {$currentCount}."
        );
    }
}
