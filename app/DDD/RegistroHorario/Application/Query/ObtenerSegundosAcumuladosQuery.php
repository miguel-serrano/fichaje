<?php

namespace App\DDD\RegistroHorario\Application\Query;

class ObtenerSegundosAcumuladosQuery
{
    public function __construct(
        public readonly string $userUuid
    ) {}
}
