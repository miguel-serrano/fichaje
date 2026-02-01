<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

use App\DDD\Administration\Domain\ValueObjects\Description;
use App\DDD\Administration\Domain\ValueObjects\PermissionId;
use App\DDD\Administration\Domain\ValueObjects\PermissionName;
use App\DDD\User\Domain\ValueObjects\UserId;

final class UpdatePermissionCommand
{
    private function __construct(
        public readonly UserId $authenticatedUserId,
        public readonly PermissionId $permissionId,
        public readonly PermissionName $name,
        public readonly ?Description $description,
    ) {
    }

    public static function create(
        int $authenticatedUserId,
        int $permissionId,
        string $name,
        ?string $description = null,
    ): self {
        return new self(
            authenticatedUserId: UserId::make($authenticatedUserId),
            permissionId: PermissionId::make($permissionId),
            name: PermissionName::make($name),
            description: null !== $description ? Description::make($description) : null,
        );
    }
}
