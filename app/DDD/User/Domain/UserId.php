<?php

namespace App\DDD\User\Domain;

use InvalidArgumentException;
use Illuminate\Support\Str;

final class UserId
{
    private string $value;

    public function __construct(string $value)
    {
        if (!Str::isUuid($value)) {
            throw new InvalidArgumentException('User ID must be a valid UUID');
        }
        $this->value = $value;
    }

    public static function generate(): self
    {
        return new self(Str::orderedUuid()->toString());
    }

    public function getValue(): string { return $this->value; }
    public function equals(UserId $other): bool { return $this->value === $other->value; }
    public function __toString(): string { return $this->value; }
}