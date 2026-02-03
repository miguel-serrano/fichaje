<?php

declare(strict_types=1);

namespace App\DDD\Shared\Domain\Event;

abstract class AbstractDomainEvent implements DomainEvent
{
    private readonly string $eventId;

    private readonly \DateTimeImmutable $occurredOn;

    public function __construct(
        private readonly string $aggregateId,
        ?string $eventId = null,
        ?\DateTimeImmutable $occurredOn = null,
    ) {
        $this->eventId = $eventId ?? uniqid('', true);
        $this->occurredOn = $occurredOn ?? new \DateTimeImmutable();
    }

    public function eventId(): string
    {
        return $this->eventId;
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    abstract public static function eventName(): string;

    /** @return array<string, mixed> */
    abstract public function toPrimitives(): array;
}
