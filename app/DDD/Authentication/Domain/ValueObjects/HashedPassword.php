<?php

namespace App\DDD\Authentication\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;

/**
 * @method static static make(string $value)
 * @method static static from(string $value)
 * @method static static makeOrNull(string|null $value)
 */
final class HashedPassword extends StringValueObject
{
    protected function validate(): void {}
}
