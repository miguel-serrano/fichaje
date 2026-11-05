<?php

namespace App\DDD\User\Application\Command;

use App\DDD\User\Domain\ValueObjects\UserId;

final class AcceptTermsCommand
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
