<?php

declare(strict_types=1);

namespace App\DDD\Shared\Domain\Event;

interface DomainEvent
{
    public function eventId(): string;

    public function occurredOn(): \DateTimeImmutable;

    public function aggregateId(): string;

    public static function eventName(): string;

    /** @return array<string, mixed> */
    public function toPrimitives(): array;
}
