<?php

declare(strict_types=1);

namespace App\DDD\Shared\Infrastructure\Persistence\Eloquent;

use App\DDD\Shared\Domain\Event\DomainEvent;
use App\DDD\Shared\Domain\Event\DomainEventRepositoryInterface;
use App\DDD\Shared\Domain\ValueObject\UnixTimestamp;
use Illuminate\Database\ConnectionInterface;

final class EloquentDomainEventRepository implements DomainEventRepositoryInterface
{
    public function __construct(private readonly ConnectionInterface $connection)
    {
    }

    public function save(DomainEvent $event): void
    {
        $this->connection->table('domain_events')->insert([
            'event_id' => $event->eventId(),
            'event_name' => $event::eventName(),
            'aggregate_id' => $event->aggregateId(),
            'payload' => json_encode($event->toPrimitives(), JSON_THROW_ON_ERROR),
            'occurred_on' => $event->occurredOn()->format('Y-m-d H:i:s'),
            'created_at' => UnixTimestamp::now()->value(),
        ]);
    }
}
