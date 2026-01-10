<?php

namespace App\DDD\Authorization\Domain\Exceptions;

class RoleNotFoundException extends \Exception
{
    public static function withId(int $id): self
    {
        return new self("Role with ID {$id} not found");
    }

    public static function withSlug(string $slug): self
    {
        return new self("Role with slug '{$slug}' not found");
    }
}
