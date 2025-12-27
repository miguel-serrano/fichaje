<?php

namespace App\DDD\RegistroHorario\ValueObjects;

class RegistroHorarioId
{
    private $value;
    public function __construct($value)
    {
        $this->value = $value;
    }
    public function value()
    {
        return $this->value;
    }
}

