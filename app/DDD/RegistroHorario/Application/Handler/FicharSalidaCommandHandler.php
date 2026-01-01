<?php

namespace App\DDD\RegistroHorario\Application\Handler;

use App\DDD\RegistroHorario\Application\Command\FicharSalidaCommand;
use App\DDD\RegistroHorario\Services\RegistroHorarioService;

class FicharSalidaCommandHandler
{
    public function __construct(
        private RegistroHorarioService $service
    ) {}

    public function handle(FicharSalidaCommand $command): void
    {
        $this->service->ficharSalida($command->userUuid, $command->registroHorarioId);
    }
}
