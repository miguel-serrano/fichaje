<?php

namespace App\DDD\Authentication\Domain\Exceptions;

final class InvalidCredentialsException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Las credenciales proporcionadas no son válidas');
    }
}
