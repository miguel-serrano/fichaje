<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

final class UpdateRoleCommand
{
    private function __construct(
        public readonly int $authenticatedUserId,
        public readonly int $roleId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly int $hierarchy,
    ) {}

    public static function create(
        int $authenticatedUserId,
        int $roleId,
        string $name,
        ?string $description = null,
        int $hierarchy = 0,
    ): self {
        return new self(
            authenticatedUserId: $authenticatedUserId,
            roleId: $roleId,
            name: $name,
            description: $description,
            hierarchy: $hierarchy,
        );
    }
}
