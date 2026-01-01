<?php

namespace App\DDD\Shared\Domain\ValueObject;

abstract class StringValueObject
{
    public function __construct(
        protected string $value
    ) {
        $this->validate($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(StringValueObject $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    protected function validate(string $value): void
    {
        // Override in child classes for specific validation
    }
}
