<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

final class CreateRoleCommand
{
    private function __construct(
        public readonly int $authenticatedUserId,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly int $hierarchy,
    ) {}

    public static function create(
        int $authenticatedUserId,
        string $name,
        string $slug,
        ?string $description = null,
        int $hierarchy = 0,
    ): self {
        return new self(
            authenticatedUserId: $authenticatedUserId,
            name: $name,
            slug: $slug,
            description: $description,
            hierarchy: $hierarchy,
        );
    }
}
