<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

final class DeleteRoleCommand
{
    private function __construct(
        public readonly int $authenticatedUserId,
        public readonly int $roleId,
    ) {}

    public static function create(int $authenticatedUserId, int $roleId): self
    {
        return new self(
            authenticatedUserId: $authenticatedUserId,
            roleId: $roleId,
        );
    }
}
