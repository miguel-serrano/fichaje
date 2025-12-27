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

    public function fichar($userId)
    {
        $registroAbierto = $this->repository->obtenerUltimoAbierto($userId);

        if ($registroAbierto) {
            // Registrar salida
            return $this->repository->cerrarRegistro($registroAbierto->id, now());
        } else {
            // Registrar nueva entrada
            return $this->repository->crearEntrada($userId, now());
        }
    }

    public function segundosAcumulados($userId)
    {
        $registros = $this->repository->obtenerRegistros($userId, now()->toDateString());
        $suma = 0;
        foreach ($registros as $registro) {
            $suma += $registro->segundosTrabajados();
        }
        return $suma;
    }
}

