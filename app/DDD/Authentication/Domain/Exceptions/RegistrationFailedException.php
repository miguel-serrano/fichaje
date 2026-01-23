<?php

namespace App\DDD\Authentication\Domain\Exceptions;

final class RegistrationFailedException extends \Exception
{
    public function __construct(string $message = 'El registro ha fallado')
    {
        parent::__construct($message);
    }
}
