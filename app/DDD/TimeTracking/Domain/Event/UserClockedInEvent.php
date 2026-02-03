<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Event;

use App\DDD\Shared\Domain\Event\AbstractDomainEvent;

final class UserClockedInEvent extends AbstractDomainEvent
{
    public function __construct(
        string $aggregateId,
        public readonly int $userId,
        public readonly int $startTime,
    ) {
        parent::__construct($aggregateId);
    }

    public static function eventName(): string
    {
        return 'time_entry.clocked_in';
    }

    /** @return array{user_id: int, start_time: int} */
    public function toPrimitives(): array
    {
        return [
            'user_id' => $this->userId,
            'start_time' => $this->startTime,
        ];
    }
}
