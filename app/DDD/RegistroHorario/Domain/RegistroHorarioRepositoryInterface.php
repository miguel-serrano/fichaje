<?php

namespace App\DDD\RegistroHorario\Domain;

use App\DDD\RegistroHorario\Domain\RegistroHorario;

interface RegistroHorarioRepositoryInterface
{
    public function crearEntrada($userUuid, $entrada): RegistroHorario;
    public function cerrarRegistro($registroId, $salida): RegistroHorario;
    public function obtenerUltimoAbierto($userUuid): ?RegistroHorario;
    public function obtenerRegistros($userUuid, $fecha = null);
}

