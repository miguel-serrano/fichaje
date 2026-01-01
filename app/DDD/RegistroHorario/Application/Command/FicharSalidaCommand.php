<?php

namespace App\DDD\RegistroHorario\Application\Command;

class FicharSalidaCommand
{
    public function __construct(
        public readonly string $userUuid,
        public readonly ?int $registroHorarioId = null
    ) {}
}
