<?php

declare(strict_types=1);

namespace App\DDD\Administration\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;

final class Description extends StringValueObject
{
    protected function validate(): void
    {
        if (strlen($this->value) > 500) {
            throw new \InvalidArgumentException('La descripción no puede exceder 500 caracteres');
        }
    }
}
