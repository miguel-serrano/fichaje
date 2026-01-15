<?php

namespace App\DDD\TimeTracking\Application\Command;

use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
use App\DDD\User\Domain\ValueObjects\Uuid;

final class ClockOutCommand
{
    private function __construct(
        public readonly Uuid $userUuid,
        public readonly ?TimeEntryId $timeEntryId,
    ) {
    }

    public static function create(string $userUuid, ?int $timeEntryId = null): self
    {
        return new self(
            userUuid: new Uuid($userUuid),
            timeEntryId: TimeEntryId::makeOrNull($timeEntryId),
        );
    }
}
