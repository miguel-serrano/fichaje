<?php

namespace App\DDD\User\Domain\Exceptions;

class MaxUsersLimitExceededException extends \Exception
{
    public function __construct(string $message = 'Maximum users limit exceeded')
    {
        parent::__construct($message);
    }
}
