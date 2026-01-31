<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

final class UpdatePermissionCommand
{
    private function __construct(
        public readonly int $authenticatedUserId,
        public readonly int $permissionId,
        public readonly string $name,
        public readonly ?string $description,
    ) {}

    public static function create(
        int $authenticatedUserId,
        int $permissionId,
        string $name,
        ?string $description = null,
    ): self {
        return new self(
            authenticatedUserId: $authenticatedUserId,
            permissionId: $permissionId,
            name: $name,
            description: $description,
        );
    }
}
