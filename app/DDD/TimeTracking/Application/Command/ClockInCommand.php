<?php

namespace App\DDD\TimeTracking\Application\Command;

use App\DDD\User\Domain\ValueObjects\Uuid;

final class ClockInCommand
{
    private function __construct(
        public readonly Uuid $userUuid,
    ) {
    }

    public static function create(string $userUuid): self
    {
        return new self(
            userUuid: new Uuid($userUuid),
        );
    }
}
