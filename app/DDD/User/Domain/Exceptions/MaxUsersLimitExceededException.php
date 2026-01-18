<?php

namespace App\DDD\User\Domain\Exceptions;

class MaxUsersLimitExceededException extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            'Cannot create more users. Maximum limit of users registered has been reached for today.'
        );
    }
}
