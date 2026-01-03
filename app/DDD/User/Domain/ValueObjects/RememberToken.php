<?php

namespace App\DDD\User\Domain\ValueObjects;

final class RememberToken
{
    private const ADMIN_TOKEN = 'soyAdm1n';

    public function __construct(private ?string $value)
    {
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function isAdmin(): bool
    {
        return $this->value === self::ADMIN_TOKEN;
    }
}
