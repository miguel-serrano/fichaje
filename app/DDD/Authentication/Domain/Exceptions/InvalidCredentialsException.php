<?php

namespace App\DDD\Authentication\Domain\Exceptions;

final class InvalidCredentialsException extends \Exception
{
    public function __construct()
    {
        parent::__construct('The provided credentials are invalid.');
    }
}
