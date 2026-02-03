<?php

namespace App\DDD\Administration\Domain\Interface;

use App\DDD\Administration\Domain\Entity\Role;
use App\DDD\Administration\Domain\ValueObjects\Description;
use App\DDD\Administration\Domain\ValueObjects\RoleHierarchy;
use App\DDD\Administration\Domain\ValueObjects\RoleId;
use App\DDD\Administration\Domain\ValueObjects\RoleName;
use App\DDD\Administration\Domain\ValueObjects\RoleSlug;
use App\DDD\User\Domain\ValueObjects\UserId;

interface RoleRepositoryInterface
{
    public function userHasRole(UserId $userId, string $roleSlug): bool;

    public function save(Role $role): Role;

    public function update(RoleId $id, RoleName $name, ?Description $description, RoleHierarchy $hierarchy): Role;

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

    public function assignRoleToUserBySystem(UserId $userId, RoleSlug $slug): void;

    public function assignRole(UserId $userId, RoleId $roleId): void;

    public function removeRole(UserId $userId, RoleId $roleId): void;

    /**
     * @return Role[]
     */
    public function userRoles(UserId $userId): array;
}
