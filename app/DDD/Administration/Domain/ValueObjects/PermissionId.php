<?php

namespace App\DDD\Administration\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\IntValueObject;

final class PermissionId extends IntValueObject
{
    protected function validate(): void
    {
        if ($this->value <= 0) {
            throw new \InvalidArgumentException('El ID de permiso debe ser un entero positivo');
        }
    }
}
