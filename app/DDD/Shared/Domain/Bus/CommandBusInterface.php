<?php

namespace App\DDD\Shared\Domain\Bus;

interface CommandBusInterface
{
    public function dispatch($command): mixed;
}
