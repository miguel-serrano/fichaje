<?php

namespace App\DDD\RegistroHorario\Repositories;

use App\DDD\RegistroHorario\Entities\RegistroHorario;

interface RegistroHorarioRepositoryInterface
{
    public function crearEntrada($userUuid, $entrada): RegistroHorario;
    public function cerrarRegistro($registroId, $salida): RegistroHorario;
    public function obtenerUltimoAbierto($userUuid): ?RegistroHorario;
    public function obtenerRegistros($userUuid, $fecha = null);
}

