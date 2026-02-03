<?php

declare(strict_types=1);

namespace App\DDD\Shared\Domain\Event;

trait RecordsDomainEvents
{
    /** @var DomainEvent[] */
    private array $domainEvents = [];

    /** @return DomainEvent[] */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    protected function recordEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }
}
