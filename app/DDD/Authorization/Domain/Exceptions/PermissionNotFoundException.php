<?php

namespace App\DDD\Authorization\Domain\Exceptions;

class PermissionNotFoundException extends \Exception
{
    public static function withId(int $id): self
    {
        return new self("Permission with ID {$id} not found");
    }

    public static function withSlug(string $slug): self
    {
        return new self("Permission with slug '{$slug}' not found");
    }
}
