<?php

namespace App\DDD\RegistroHorario\Application\Query;

class HasOpenRegistroQuery
{
    public function __construct(
        public readonly string $userUuid
    ) {}
}
