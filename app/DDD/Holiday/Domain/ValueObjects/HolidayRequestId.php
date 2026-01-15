<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\IntValueObject;

/**
 * @method static static make(int $value)
 * @method static static from(int $value)
 * @method static static makeOrNull(int|null $value)
 */
final class HolidayRequestId extends IntValueObject
{
    protected function validate(): void
    {
        if ($this->value <= 0) {
            throw new \InvalidArgumentException('Holiday Request ID must be a positive integer');
        }
    }
}
