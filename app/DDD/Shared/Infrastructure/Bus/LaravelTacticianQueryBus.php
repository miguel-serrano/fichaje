<?php

namespace App\DDD\Shared\Infrastructure\Bus;

use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use Joselfonseca\LaravelTactician\CommandBusInterface as TacticianCommandBusInterface;

class LaravelTacticianQueryBus implements QueryBusInterface
{
    public function __construct(
        private TacticianCommandBusInterface $commandBus
    ) {}

    public function dispatch($query): mixed
    {
        return $this->commandBus->dispatch($query);
    }
}
