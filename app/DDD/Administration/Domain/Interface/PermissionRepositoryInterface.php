<?php

namespace App\DDD\Administration\Domain\Interface;

use App\DDD\Administration\Domain\Entity\Permission;
use App\DDD\Administration\Domain\ValueObjects\BoundedContext;
use App\DDD\Administration\Domain\ValueObjects\PermissionId;
use App\DDD\Administration\Domain\ValueObjects\PermissionSlug;
use App\DDD\User\Domain\ValueObjects\UserId;

interface PermissionRepositoryInterface
{
    public function userHasPermission(UserId $userId, string $permissionSlug): bool;

    public function save(Permission $permission): Permission;

    public function findById(PermissionId $id): ?Permission;

    public function findByIdOrFail(PermissionId $id): Permission;

    public function findBySlug(PermissionSlug $slug): ?Permission;

    public function findBySlugOrFail(PermissionSlug $slug): Permission;

    /**
     * @return Permission[]
     */
    public function findAll(): array;

    /**
     * @return Permission[]
     */
    public function findByBoundedContext(BoundedContext $context): array;

    public function delete(PermissionId $id): bool;

    /**
     * @return string[]
     */
    public function userPermissions(UserId $userId): array;
}
