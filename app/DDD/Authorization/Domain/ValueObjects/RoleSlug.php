<?php

namespace App\DDD\Authorization\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;

final class RoleSlug extends StringValueObject
{
    protected function validate(): void
    {
        if (empty($this->value)) {
            throw new \InvalidArgumentException('El slug del rol no puede estar vacío');
        }

        if (!preg_match('/^[a-z][a-z0-9_]*$/', $this->value)) {
            throw new \InvalidArgumentException('El slug del rol debe empezar con una letra y contener solo letras minúsculas, números y guiones bajos');
        }
    }
}
