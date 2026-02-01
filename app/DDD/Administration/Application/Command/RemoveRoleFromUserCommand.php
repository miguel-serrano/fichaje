<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

use App\DDD\Administration\Domain\ValueObjects\RoleSlug;
use App\DDD\User\Domain\ValueObjects\UserId;

final class RemoveRoleFromUserCommand
{
    private function __construct(
        public readonly UserId $authenticatedUserId,
        public readonly UserId $targetUserId,
        public readonly RoleSlug $roleSlug,
    ) {
    }

    public static function create(int $authenticatedUserId, int $targetUserId, string $roleSlug): self
    {
        return new self(
            authenticatedUserId: UserId::make($authenticatedUserId),
            targetUserId: UserId::make($targetUserId),
            roleSlug: RoleSlug::make($roleSlug),
        );
    }
}
