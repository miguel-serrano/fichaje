<?php

namespace App\DDD\RegistroHorario\Application\Handler;

use App\DDD\RegistroHorario\Application\Command\FicharEntradaCommand;
use App\DDD\RegistroHorario\Services\RegistroHorarioService;

class FicharEntradaCommandHandler
{
    public function __construct(
        private RegistroHorarioService $service
    ) {}

    public function handle(FicharEntradaCommand $command): void
    {
        $this->service->ficharEntrada($command->userUuid);
    }
}
