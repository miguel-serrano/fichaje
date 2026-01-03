<?php

namespace App\DDD\User\Domain\Exceptions;

use Exception;

class CannotDeleteAdminUserException extends Exception
{
    public function __construct(string $message = 'No se puede eliminar un usuario administrador', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
