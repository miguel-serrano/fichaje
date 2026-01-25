<?php

namespace App\DDD\Notification\Application\Command;

use App\DDD\User\Domain\ValueObjects\UserId;

final class MarkAllNotificationsAsReadCommand
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
