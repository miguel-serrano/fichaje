<?php

namespace App\DDD\RegistroHorario\Application;

use App\DDD\RegistroHorario\Services\RegistroHorarioService;

class FicharSalida
{
    private $service;
    public function __construct(RegistroHorarioService $service)
    {
        $this->service = $service;
    }
    public function handle($userUuid)
    {
        return $this->service->ficharSalida($userUuid);
    }
}

