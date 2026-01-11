<?php

namespace App\DDD\Authorization\Domain\Interface;

use App\DDD\Authorization\Domain\Entity\Role;
use App\DDD\Authorization\Domain\ValueObjects\RoleId;
use App\DDD\Authorization\Domain\ValueObjects\RoleSlug;
use App\DDD\User\Domain\ValueObjects\UserId;

interface RoleRepositoryInterface
{
    public function userHasRole(UserId $userId, string $roleSlug): bool;

    public function save(Role $role): Role;

    public function findById(RoleId $id): ?Role;

    public function findByIdOrFail(RoleId $id): Role;

    public function findBySlug(RoleSlug $slug): ?Role;

    public function findBySlugOrFail(RoleSlug $slug): Role;

    /**
     * @return Role[]
     */
    public function findAll(): array;

    public function delete(RoleId $id): bool;

    /**
     * @param int[] $permissionIds
     */
    public function syncPermissions(RoleId $roleId, array $permissionIds): void;
}
