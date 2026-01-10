<?php

namespace App\DDD\Authorization\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;

final class RoleName extends StringValueObject
{
    protected function validate(): void
    {
        if (empty(trim($this->value))) {
            throw new \InvalidArgumentException('Role name cannot be empty');
        }

        if (strlen($this->value) > 100) {
            throw new \InvalidArgumentException('Role name cannot exceed 100 characters');
        }
    }
}
