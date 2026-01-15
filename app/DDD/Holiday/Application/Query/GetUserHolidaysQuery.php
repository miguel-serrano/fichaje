<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Query;

use App\DDD\User\Domain\ValueObjects\UserId;

final class GetUserHolidaysQuery
{
    private function __construct(
        public readonly UserId $userId,
    ) {
    }

    public static function create(int $userId): self
    {
        return new self(
            userId: UserId::make($userId),
        );
    }
}
