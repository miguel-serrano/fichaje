<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Event;

use App\DDD\Shared\Domain\Event\AbstractDomainEvent;

final class UserClockedOutEvent extends AbstractDomainEvent
{
    public function __construct(
        string $aggregateId,
        public readonly int $userId,
        public readonly int $endTime,
    ) {
        parent::__construct($aggregateId);
    }

    public static function eventName(): string
    {
        return 'time_entry.clocked_out';
    }

    /** @return array{user_id: int, end_time: int} */
    public function toPrimitives(): array
    {
        return [
            'user_id' => $this->userId,
            'end_time' => $this->endTime,
        ];
    }
}
