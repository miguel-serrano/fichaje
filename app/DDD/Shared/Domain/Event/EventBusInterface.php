<?php

declare(strict_types=1);

namespace App\DDD\Shared\Domain\Event;

interface EventBusInterface
{
    public function publish(DomainEvent ...$events): void;
}
