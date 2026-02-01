<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

use App\DDD\Administration\Domain\ValueObjects\PermissionIdCollection;
use App\DDD\Administration\Domain\ValueObjects\RoleId;
use App\DDD\User\Domain\ValueObjects\UserId;

final class SyncPermissionsToRoleCommand
{
    private function __construct(
        public readonly UserId $authenticatedUserId,
        public readonly RoleId $roleId,
        public readonly PermissionIdCollection $permissionIds,
    ) {
    }

    /**
     * @param int[] $permissionIds
     */
    public static function create(int $authenticatedUserId, int $roleId, array $permissionIds): self
    {
        return new self(
            authenticatedUserId: UserId::make($authenticatedUserId),
            roleId: RoleId::make($roleId),
            permissionIds: PermissionIdCollection::fromPrimitives($permissionIds),
        );
    }
}
