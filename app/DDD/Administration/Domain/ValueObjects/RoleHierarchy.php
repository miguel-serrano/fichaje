<?php

declare(strict_types=1);

namespace App\DDD\Administration\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\IntValueObject;

final class RoleHierarchy extends IntValueObject
{
    protected function validate(): void
    {
        if ($this->value < 0) {
            throw new \InvalidArgumentException('La jerarquía del rol no puede ser negativa');
        }
    }
}
