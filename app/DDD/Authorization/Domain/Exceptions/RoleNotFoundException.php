<?php

namespace App\DDD\Authorization\Domain\Exceptions;

class RoleNotFoundException extends \Exception
{
    public static function withId(int $id): self
    {
        return new self("Rol con ID {$id} no encontrado");
    }

    public static function withSlug(string $slug): self
    {
        return new self("Rol con slug '{$slug}' no encontrado");
    }
}
