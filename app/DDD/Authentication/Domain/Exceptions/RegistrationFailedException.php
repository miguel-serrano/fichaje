<?php

namespace App\DDD\Authentication\Domain\Exceptions;

use Exception;

final class RegistrationFailedException extends Exception
{
    public function __construct(string $message = 'Registration failed.')
    {
        parent::__construct($message);
    }
}
