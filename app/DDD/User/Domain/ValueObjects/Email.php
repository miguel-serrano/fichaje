<?php

namespace App\DDD\User\Domain\ValueObjects;

use InvalidArgumentException;
use MichaelRubel\ValueObjects\Collection\Complex\Email as BaseEmail;

/**
 * @method static static make(string $value)
 * @method static static from(string $value)
 * @method static static makeOrNull(string|null $value)
 */
final class Email extends BaseEmail
{
    protected function validate(): void
    {
        if (! filter_var($this->value(), FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
    }

    protected function sanitize(): void
    {
        $this->value = strtolower(trim($this->value()));
    }
}
