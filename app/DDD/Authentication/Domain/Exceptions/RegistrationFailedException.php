<?php

namespace App\DDD\Authentication\Domain\Exceptions;

final class RegistrationFailedException extends \Exception
{
    public function __construct(string $message = 'Registration failed.')
    {
        parent::__construct($message);
    }
}
