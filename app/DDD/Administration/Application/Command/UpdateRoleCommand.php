<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

use App\DDD\Administration\Domain\ValueObjects\Description;
use App\DDD\Administration\Domain\ValueObjects\RoleHierarchy;
use App\DDD\Administration\Domain\ValueObjects\RoleId;
use App\DDD\Administration\Domain\ValueObjects\RoleName;
use App\DDD\User\Domain\ValueObjects\UserId;

final class UpdateRoleCommand
{
    private function __construct(
        public readonly UserId $authenticatedUserId,
        public readonly RoleId $roleId,
        public readonly RoleName $name,
        public readonly ?Description $description,
        public readonly RoleHierarchy $hierarchy,
    ) {
    }

    public static function create(
        int $authenticatedUserId,
        int $roleId,
        string $name,
        ?string $description = null,
        int $hierarchy = 0,
    ): self {
        return new self(
            authenticatedUserId: UserId::make($authenticatedUserId),
            roleId: RoleId::make($roleId),
            name: RoleName::make($name),
            description: null !== $description ? Description::make($description) : null,
            hierarchy: RoleHierarchy::make($hierarchy),
        );
    }
}
