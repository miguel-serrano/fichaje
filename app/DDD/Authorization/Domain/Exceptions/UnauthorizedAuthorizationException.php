<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Domain\Exceptions;

final class UnauthorizedAuthorizationException extends \Exception
{
    public static function forManageRoles(): self
    {
        return new self('No tienes permisos para gestionar roles');
    }

    public static function forManagePermissions(): self
    {
        return new self('No tienes permisos para gestionar permisos');
    }

    public static function forAssignRoles(): self
    {
        return new self('No tienes permisos para asignar roles');
    }
}
