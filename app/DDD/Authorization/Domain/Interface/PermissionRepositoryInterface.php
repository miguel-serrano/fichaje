<?php

namespace App\DDD\Authorization\Domain\Interface;

use App\DDD\Authorization\Domain\Entity\Permission;
use App\DDD\Authorization\Domain\ValueObjects\BoundedContext;
use App\DDD\Authorization\Domain\ValueObjects\PermissionId;
use App\DDD\Authorization\Domain\ValueObjects\PermissionSlug;

interface PermissionRepositoryInterface
{
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
}
