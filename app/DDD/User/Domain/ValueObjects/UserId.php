<?php

namespace App\DDD\User\Domain\ValueObjects;

use InvalidArgumentException;

final class UserId
{
    private string $value;

    public function __construct(string|int $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('User ID cannot be empty');
        }
        $this->value = (string) $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}