<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

use App\DDD\Administration\Domain\ValueObjects\PermissionId;
use App\DDD\User\Domain\ValueObjects\UserId;

final class DeletePermissionCommand
{
    private function __construct(
        public readonly UserId $authenticatedUserId,
        public readonly PermissionId $permissionId,
    ) {
    }

    public static function create(int $authenticatedUserId, int $permissionId): self
    {
        return new self(
            authenticatedUserId: UserId::make($authenticatedUserId),
            permissionId: PermissionId::make($permissionId),
        );
    }
}
