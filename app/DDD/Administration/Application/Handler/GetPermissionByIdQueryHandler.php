<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Query\GetPermissionByIdQuery;
use App\DDD\Administration\Domain\Interface\PermissionRepositoryInterface;

class GetPermissionByIdQueryHandler
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
    ) {
    }

    /**
     * @return array{id: int|null, name: string, slug: string, bounded_context: string, description: string|null, is_system: bool}
     */
    public function handle(GetPermissionByIdQuery $query): array
    {
        $permission = $this->permissionRepository->findByIdOrFail($query->permissionId);

        return $permission->toArray();
    }
}
