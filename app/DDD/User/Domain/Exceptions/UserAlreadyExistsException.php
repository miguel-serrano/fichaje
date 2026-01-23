<?php

namespace App\DDD\User\Domain\Exceptions;

use App\DDD\User\Domain\ValueObjects\Email;

class UserAlreadyExistsException extends \Exception
{
    public function __construct(Email $email)
    {
        parent::__construct("El usuario con email '{$email->value()}' ya existe");
    }
}
