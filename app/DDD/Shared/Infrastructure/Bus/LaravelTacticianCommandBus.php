<?php

namespace App\DDD\Shared\Infrastructure\Bus;

use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use Joselfonseca\LaravelTactician\CommandBusInterface as TacticianCommandBusInterface;

class LaravelTacticianCommandBus implements CommandBusInterface
{
    public function __construct(
        private TacticianCommandBusInterface $commandBus
    ) {}

    public function dispatch($command): mixed
    {
        return $this->commandBus->dispatch($command);
    }
}
