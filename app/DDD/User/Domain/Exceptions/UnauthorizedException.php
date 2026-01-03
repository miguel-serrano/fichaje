<?php

namespace App\DDD\User\Domain\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    public function __construct(string $message = 'No tienes permisos para realizar esta acción', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function forToggleActive(): self
    {
        return new self('No tienes permisos para cambiar el estado de este usuario');
    }

    public static function forView(): self
    {
        return new self('No tienes permisos para ver este usuario');
    }

    public static function forCreate(): self
    {
        return new self('No tienes permisos para crear usuarios');
    }

    public static function forUpdate(): self
    {
        return new self('No tienes permisos para actualizar este usuario');
    }

    public static function forDelete(): self
    {
        return new self('No tienes permisos para eliminar este usuario');
    }
}
