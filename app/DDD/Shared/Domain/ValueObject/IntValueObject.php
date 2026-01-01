<?php

namespace App\DDD\Shared\Domain\ValueObject;

abstract class IntValueObject
{
    public function __construct(
        protected int $value
    ) {
        $this->validate($value);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(IntValueObject $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    protected function validate(int $value): void
    {
        // Override in child classes for specific validation
    }
}
