<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

use App\DDD\Administration\Domain\ValueObjects\RoleId;
use App\DDD\User\Domain\ValueObjects\UserId;

final class DeleteRoleCommand
{
    private function __construct(
        public readonly UserId $authenticatedUserId,
        public readonly RoleId $roleId,
    ) {
    }

    public static function create(int $authenticatedUserId, int $roleId): self
    {
        return new self(
            authenticatedUserId: UserId::make($authenticatedUserId),
            roleId: RoleId::make($roleId),
        );
    }
}
