<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\IntValueObject;

final class DaysRange extends IntValueObject
{
    protected function validate(): void
    {
        if ($this->value < 0) {
            throw new \InvalidArgumentException('El rango de días no puede ser negativo');
        }
    }
}
