<?php

namespace App\DDD\Authentication\Domain\Exceptions;

final class UserNotAuthenticatedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('User is not authenticated.');
    }
}
