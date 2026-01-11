<?php

namespace App\DDD\Authorization\Application\Command;

final class CreateRoleCommand
{
    public function __construct(
        public int $authenticatedUserId,
        public string $name,
        public string $slug,
        public ?string $description,
        public int $hierarchy = 0,
    ) {
    }
}
