<?php

namespace App\DDD\User\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\IntValueObject;

/**
 * @method static static make(int $value)
 * @method static static from(int $value)
 * @method static static makeOrNull(int|null $value)
 */
final class UserId extends IntValueObject
{
    protected function validate(): void
    {
        if ($this->value <= 0) {
            throw new \InvalidArgumentException('User ID must be a positive integer');
        }
    }
}
