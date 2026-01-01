<?php

namespace App\DDD\RegistroHorario\Application\Handler;

use App\DDD\RegistroHorario\Application\Query\ObtenerSegundosAcumuladosQuery;
use App\DDD\RegistroHorario\Services\RegistroHorarioService;

class ObtenerSegundosAcumuladosQueryHandler
{
    public function __construct(
        private RegistroHorarioService $service
    ) {}

    public function handle(ObtenerSegundosAcumuladosQuery $query): int
    {
        return $this->service->segundosAcumulados($query->userUuid);
    }
}
