<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

final class CreatePermissionCommand
{
    private function __construct(
        public readonly int $authenticatedUserId,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $boundedContext,
        public readonly ?string $description,
    ) {}

    public static function create(
        int $authenticatedUserId,
        string $name,
        string $slug,
        string $boundedContext,
        ?string $description = null,
    ): self {
        return new self(
            authenticatedUserId: $authenticatedUserId,
            name: $name,
            slug: $slug,
            boundedContext: $boundedContext,
            description: $description,
        );
    }
}
