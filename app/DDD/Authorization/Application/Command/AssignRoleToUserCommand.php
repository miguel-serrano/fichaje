<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Application\Command;

final class AssignRoleToUserCommand
{
    private function __construct(
        public readonly int $authenticatedUserId,
        public readonly int $targetUserId,
        public readonly string $roleSlug,
    ) {
    }

    public static function create(int $authenticatedUserId, int $targetUserId, string $roleSlug): self
    {
        return new self(
            authenticatedUserId: $authenticatedUserId,
            targetUserId: $targetUserId,
            roleSlug: $roleSlug,
        );
    }
}
