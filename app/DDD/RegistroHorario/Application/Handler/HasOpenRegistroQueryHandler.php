<?php

namespace App\DDD\RegistroHorario\Application\Handler;

use App\DDD\RegistroHorario\Application\Query\HasOpenRegistroQuery;
use App\DDD\RegistroHorario\Services\RegistroHorarioService;

class HasOpenRegistroQueryHandler
{
    public function __construct(
        private RegistroHorarioService $service
    ) {}

    public function handle(HasOpenRegistroQuery $query): bool
    {
        return $this->service->hasOpenRegistro($query->userUuid);
    }
}
