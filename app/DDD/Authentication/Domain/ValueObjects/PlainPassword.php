<?php

namespace App\DDD\Authentication\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;
use InvalidArgumentException;

final class PlainPassword extends StringValueObject
{
    protected function validate(string $value): void
    {
        if (strlen($value) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long.');
        }
    }
}
