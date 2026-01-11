<?php

namespace App\DDD\Authorization\Domain\Exceptions;

class CannotDeleteSystemRoleException extends \Exception
{
    public static function forRole(string $slug): self
    {
        return new self("No se puede eliminar el rol de sistema '{$slug}'");
    }
}
