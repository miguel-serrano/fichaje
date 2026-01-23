<?php

namespace App\DDD\Authorization\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;

final class PermissionName extends StringValueObject
{
    protected function validate(): void
    {
        if (empty(trim($this->value))) {
            throw new \InvalidArgumentException('El nombre del permiso no puede estar vacío');
        }

        if (strlen($this->value) > 100) {
            throw new \InvalidArgumentException('El nombre del permiso no puede exceder 100 caracteres');
        }
    }
}
