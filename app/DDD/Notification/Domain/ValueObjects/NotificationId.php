<?php

namespace App\DDD\Notification\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\IntValueObject;

/**
 * @method static static make(int $value)
 * @method static static from(int $value)
 * @method static static makeOrNull(int|null $value)
 */
final class NotificationId extends IntValueObject
{
    protected function validate(): void
    {
        if ($this->value <= 0) {
            throw new \InvalidArgumentException('El ID de notificación debe ser un entero positivo');
        }
    }
}
