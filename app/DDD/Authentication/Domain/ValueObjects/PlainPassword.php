<?php

namespace App\DDD\Authentication\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;
use InvalidArgumentException;

/**
 * @method static static make(string $value)
 * @method static static from(string $value)
 * @method static static makeOrNull(string|null $value)
 */
final class PlainPassword extends StringValueObject
{
    protected function validate(): void
    {
        if (strlen($this->value) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long.');
        }
    }
}
