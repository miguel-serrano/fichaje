<?php

namespace App\DDD\Shared\Domain\Bus;

interface QueryBusInterface
{
    public function dispatch($query): mixed;
}
