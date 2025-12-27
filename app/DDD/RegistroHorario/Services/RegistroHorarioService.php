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

    public function ficharEntrada($userUuid)
    {
        return $this->repository->crearEntrada($userUuid, now());
    }

    public function ficharSalida($userUuid)
    {
        $registroAbierto = $this->repository->obtenerUltimoAbierto($userUuid);
        
        if (!$registroAbierto) {
            throw new \InvalidArgumentException('No hay registro de entrada abierto para cerrar');
        }
        
        return $this->repository->cerrarRegistro($registroAbierto->id, now());
    }

    public function obtenerUltimoRegistro($userUuid)
    {
        return $this->repository->obtenerUltimoAbierto($userUuid);
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

