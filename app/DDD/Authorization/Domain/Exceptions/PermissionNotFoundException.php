<?php

namespace App\DDD\Authorization\Domain\Exceptions;

class PermissionNotFoundException extends \Exception
{
    public static function withId(int $id): self
    {
        return new self("Permiso con ID {$id} no encontrado");
    }

    public static function withSlug(string $slug): self
    {
        return new self("Permiso con slug '{$slug}' no encontrado");
    }
}
