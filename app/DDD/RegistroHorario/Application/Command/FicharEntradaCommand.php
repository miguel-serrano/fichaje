<?php

namespace App\DDD\RegistroHorario\Application\Command;

class FicharEntradaCommand
{
    public function __construct(
        public readonly string $userUuid
    ) {}
}
