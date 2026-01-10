<?php

namespace App\DDD\Authorization\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;

final class RoleSlug extends StringValueObject
{
    protected function validate(): void
    {
        if (empty($this->value)) {
            throw new \InvalidArgumentException('Role slug cannot be empty');
        }

        if (!preg_match('/^[a-z][a-z0-9_]*$/', $this->value)) {
            throw new \InvalidArgumentException('Role slug must start with a letter and contain only lowercase letters, numbers, and underscores');
        }
    }

    public function equals(RoleSlug $other): bool
    {
        return $this->value === $other->value();
    }
}
