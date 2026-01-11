<?php

namespace App\DDD\User\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;

/**
 * @method static static make(string|int $value)
 * @method static static from(string|int $value)
 * @method static static makeOrNull(string|int|null $value)
 */
final class UserId extends StringValueObject
{
    public function __construct(string|int $value)
    {
        parent::__construct((string) $value);
    }

    protected function validate(): void
    {
        if (empty($this->value)) {
            throw new \InvalidArgumentException('User ID cannot be empty');
        }
    }
}
