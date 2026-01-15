<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Command;

use App\DDD\Holiday\Domain\ValueObjects\HolidayRequestId;
use App\DDD\User\Domain\ValueObjects\UserId;

final class RejectHolidayRequestCommand
{
    private function __construct(
        public readonly UserId $authenticatedUserId,
        public readonly HolidayRequestId $holidayRequestId,
    ) {
    }

    public static function create(int $authenticatedUserId, int $holidayRequestId): self
    {
        return new self(
            authenticatedUserId: UserId::make($authenticatedUserId),
            holidayRequestId: HolidayRequestId::make($holidayRequestId),
        );
    }
}
