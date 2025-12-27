<?php

namespace App\DDD\RegistroHorario\Repositories;

use App\DDD\RegistroHorario\Entities\RegistroHorario as RegistroHorarioEntity;
use App\Models\RegistroHorario;

class RegistroHorarioRepositoryEloquent implements RegistroHorarioRepositoryInterface
{
    public function crearEntrada($userUuid, $entrada): RegistroHorarioEntity
    {
        $registro = RegistroHorario::create([
            'user_id' => $userUuid,
            'entrada' => $entrada,
        ]);
        return $this->toEntity($registro);
    }

    public function cerrarRegistro($registroId, $salida): RegistroHorarioEntity
    {
        $registro = RegistroHorario::findOrFail($registroId);
        $registro->salida = $salida;
        $registro->save();
        return $this->toEntity($registro);
    }

    public function obtenerUltimoAbierto($userUuid): ?RegistroHorarioEntity
    {
        $registro = RegistroHorario::where('user_id', $userUuid)
            ->whereNull('salida')
            ->latest('entrada')
            ->first();
        return $registro ? $this->toEntity($registro) : null;
    }

    public function obtenerRegistros($userUuid, $fecha = null)
    {
        $query = RegistroHorario::where('user_id', $userUuid);
        if ($fecha) {
            $query->whereDate('entrada', $fecha);
        }
        $result = [];
        foreach ($query->get() as $registro) {
            $result[] = $this->toEntity($registro);
        }
        return $result;
    }

    private function toEntity($registro): RegistroHorarioEntity
    {
        return new RegistroHorarioEntity(
            $registro->id,
            $registro->user_id,
            $registro->entrada,
            $registro->salida
        );
    }
}

