<?php

namespace App\DDD\RegistroHorario\Application;

use App\DDD\RegistroHorario\Services\RegistroHorarioService;

class ObtenerSegundosAcumulados
{
    private $service;
    public function __construct(RegistroHorarioService $service)
    {
        $this->service = $service;
    }
    public function handle($userUuid)
    {
        return $this->service->segundosAcumulados($userUuid);
    }
}

