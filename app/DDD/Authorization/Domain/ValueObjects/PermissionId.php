<?php

namespace App\DDD\Authorization\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\IntValueObject;

final class PermissionId extends IntValueObject
{
    protected function validate(): void
    {
        if ($this->value <= 0) {
            throw new \InvalidArgumentException('PermissionId must be a positive integer');
        }
    }
}
