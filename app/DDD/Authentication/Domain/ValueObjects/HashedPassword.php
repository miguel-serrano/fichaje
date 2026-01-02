<?php

namespace App\DDD\Authentication\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;

final class HashedPassword extends StringValueObject
{
    protected function validate(string $value): void
    {
        // Hashed passwords are already validated
    }
}
