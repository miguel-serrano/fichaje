<?php

namespace App\DDD\User\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;

/**
 * @method static static make(string $value)
 * @method static static from(string $value)
 * @method static static makeOrNull(string|null $value)
 */
final class Email extends StringValueObject
{
    public function __construct(string $value)
    {
        // Sanitizar ANTES de asignar el valor (inmutabilidad)
        $sanitizedValue = strtolower(trim($value));

        parent::__construct($sanitizedValue);
    }

    protected function validate(): void
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Formato de email inválido');
        }
    }

    public function domain(): string
    {
        return explode('@', $this->value)[1] ?? '';
    }

    public function localPart(): string
    {
        return explode('@', $this->value)[0] ?? '';
    }
}
