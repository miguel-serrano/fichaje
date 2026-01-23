<?php

namespace App\DDD\User\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;

/**
 * @method static static make(string $value)
 * @method static static from(string $value)
 * @method static static makeOrNull(string|null $value)
 */
final class Name extends StringValueObject
{
    protected function validate(): void
    {
        if ('' === trim($this->value)) {
            throw new \InvalidArgumentException('El nombre no puede estar vacío');
        }

        if (strlen($this->value) > 255) {
            throw new \InvalidArgumentException('El nombre no puede exceder 255 caracteres');
        }
    }
}
