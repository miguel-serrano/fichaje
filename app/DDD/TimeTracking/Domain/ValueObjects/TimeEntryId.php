<?php

namespace App\DDD\TimeTracking\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\IntValueObject;

/**
 * @method static static make(int $value)
 * @method static static from(int $value)
 * @method static static makeOrNull(int|null $value)
 */
final class TimeEntryId extends IntValueObject
{
    protected function validate(): void
    {
        if ($this->value <= 0) {
            throw new \InvalidArgumentException('El ID de entrada debe ser un entero positivo');
        }
    }
}
