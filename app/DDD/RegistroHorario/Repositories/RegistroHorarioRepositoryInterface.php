<?php

namespace App\DDD\RegistroHorario\Repositories;

use App\DDD\RegistroHorario\Entities\RegistroHorario;

interface RegistroHorarioRepositoryInterface
{
    public function crearEntrada($userId, $entrada): RegistroHorario;
    public function cerrarRegistro($registroId, $salida): RegistroHorario;
    public function obtenerUltimoAbierto($userId): ?RegistroHorario;
    public function obtenerRegistros($userId, $fecha = null);
}

