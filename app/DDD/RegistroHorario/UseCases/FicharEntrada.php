<?php

namespace App\DDD\RegistroHorario\UseCases;

use App\DDD\RegistroHorario\Services\RegistroHorarioService;

class FicharEntrada
{
    private $service;
    public function __construct(RegistroHorarioService $service)
    {
        $this->service = $service;
    }
    public function handle($userId)
    {
        return $this->service->fichar($userId);
    }
}

