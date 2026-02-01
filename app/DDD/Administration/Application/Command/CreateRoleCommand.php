<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

use App\DDD\Administration\Domain\ValueObjects\Description;
use App\DDD\Administration\Domain\ValueObjects\RoleHierarchy;
use App\DDD\Administration\Domain\ValueObjects\RoleName;
use App\DDD\Administration\Domain\ValueObjects\RoleSlug;
use App\DDD\User\Domain\ValueObjects\UserId;

final class CreateRoleCommand
{
    private function __construct(
        public readonly UserId $authenticatedUserId,
        public readonly RoleName $name,
        public readonly RoleSlug $slug,
        public readonly ?Description $description,
        public readonly RoleHierarchy $hierarchy,
    ) {
    }

    public static function create(
        int $authenticatedUserId,
        string $name,
        string $slug,
        ?string $description = null,
        int $hierarchy = 0,
    ): self {
        return new self(
            authenticatedUserId: UserId::make($authenticatedUserId),
            name: RoleName::make($name),
            slug: RoleSlug::make($slug),
            description: null !== $description ? Description::make($description) : null,
            hierarchy: RoleHierarchy::make($hierarchy),
        );
    }
}
