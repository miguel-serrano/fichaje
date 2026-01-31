<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Query\GetAllPermissionsQuery;
use App\DDD\Administration\Domain\Entity\Permission;
use App\DDD\Administration\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Administration\Domain\ValueObjects\BoundedContext;

class GetAllPermissionsQueryHandler
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
    ) {}

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
