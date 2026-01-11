<?php

namespace App\DDD\Authorization\Application\Command;

final class CreatePermissionCommand
{
    public function __construct(
        public int $authenticatedUserId,
        public string $name,
        public string $slug,
        public string $boundedContext,
        public ?string $description,
    ) {
    }
}
