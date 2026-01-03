<?php

namespace App\DDD\User\Domain\ValueObjects;

use Illuminate\Support\Str;
use InvalidArgumentException;

final class Uuid
{
    private string $value;

    public function __construct(string $value)
    {
        if (! Str::isUuid($value)) {
            throw new InvalidArgumentException('UUID must be a valid UUID');
        }
        $this->value = $value;
    }

    public static function generate(): self
    {
        return new self(Str::orderedUuid()->toString());
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(Uuid $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
