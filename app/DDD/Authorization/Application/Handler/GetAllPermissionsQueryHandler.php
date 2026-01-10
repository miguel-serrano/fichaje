<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Query\GetAllPermissionsQuery;
use App\DDD\Authorization\Domain\Entity\Permission;
use App\DDD\Authorization\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Authorization\Domain\ValueObjects\BoundedContext;

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
            $permissions = $this->permissionRepository->findByBoundedContext(
                BoundedContext::from($query->boundedContext)
            );
        } else {
            $permissions = $this->permissionRepository->findAll();
        }

        return array_map(
            fn (Permission $permission) => $permission->toArray(),
            $permissions
        );
    }
}
