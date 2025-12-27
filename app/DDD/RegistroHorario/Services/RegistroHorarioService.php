<?php

namespace App\DDD\RegistroHorario\Services;

use App\DDD\RegistroHorario\Repositories\RegistroHorarioRepositoryInterface;

class RegistroHorarioService
{
    protected $repository;

    public function __construct(RegistroHorarioRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function fichar($userUuid)
    {
        $registroAbierto = $this->repository->obtenerUltimoAbierto($userUuid);

        if ($registroAbierto) {
            // Registrar salida
            return $this->repository->cerrarRegistro($registroAbierto->id, now());
        } else {
            // Registrar nueva entrada
            return $this->repository->crearEntrada($userUuid, now());
        }
    }

    public function segundosAcumulados($userUuid)
    {
        $registros = $this->repository->obtenerRegistros($userUuid, now()->toDateString());
        $suma = 0;
        foreach ($registros as $registro) {
            $suma += $registro->segundosTrabajados();
        }
        return $suma;
    }
}

