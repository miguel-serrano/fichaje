<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Event;

use App\DDD\Shared\Domain\Event\AbstractDomainEvent;

final class HolidayRequestApprovedEvent extends AbstractDomainEvent
{
    public function __construct(
        string $aggregateId,
        public readonly int $userId,
        public readonly int $startDate,
        public readonly int $endDate,
    ) {
        parent::__construct($aggregateId);
    }

    public static function eventName(): string
    {
        return 'holiday_request.approved';
    }

    /** @return array{user_id: int, start_date: int, end_date: int} */
    public function toPrimitives(): array
    {
        return [
            'user_id' => $this->userId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];
    }
}
