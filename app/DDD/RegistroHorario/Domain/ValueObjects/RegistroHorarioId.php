<?php

namespace App\DDD\RegistroHorario\Domain\ValueObjects;

final class RegistroHorarioId
{
    private int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}