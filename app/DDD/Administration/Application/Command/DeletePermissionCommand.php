<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

final class DeletePermissionCommand
{
    private function __construct(
        public readonly int $authenticatedUserId,
        public readonly int $permissionId,
    ) {}

    public static function create(int $authenticatedUserId, int $permissionId): self
    {
        return new self(
            authenticatedUserId: $authenticatedUserId,
            permissionId: $permissionId,
        );
    }
}
