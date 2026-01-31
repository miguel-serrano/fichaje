<?php

namespace App\DDD\Administration\Domain\Exceptions;

class CannotDeleteSystemPermissionException extends \Exception
{
    public static function forPermission(string $slug): self
    {
        return new self("No se puede eliminar el permiso de sistema '{$slug}'");
    }
}
