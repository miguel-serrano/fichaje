<?php

declare(strict_types=1);

namespace App\DDD\Shared\Domain\Event;

interface DomainEventRepositoryInterface
{
    public function save(DomainEvent $event): void;
}
