<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Query\GetAllPermissionsQuery;
use App\DDD\Administration\Domain\Entity\Permission;
use App\DDD\Administration\Domain\Interface\PermissionRepositoryInterface;

class GetAllPermissionsQueryHandler
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
    ) {
    }

    /**
     * @return array<array{id: int|null, name: string, slug: string, bounded_context: string, description: string|null, is_system: bool}>
     */
    public function handle(GetAllPermissionsQuery $query): array
    {
        if ($query->boundedContext) {
            $permissions = $this->permissionRepository->findByBoundedContext($query->boundedContext);
        } else {
            $permissions = $this->permissionRepository->findAll();
        }

        return array_map(
            fn (Permission $permission) => $permission->toArray(),
            $permissions
        );
    }
}
