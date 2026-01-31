<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Query\GetPermissionByIdQuery;
use App\DDD\Administration\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Administration\Domain\ValueObjects\PermissionId;

class GetPermissionByIdQueryHandler
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
    ) {}

    /**
     * @return array{id: int|null, name: string, slug: string, bounded_context: string, description: string|null, is_system: bool}
     */
    public function handle(GetPermissionByIdQuery $query): array
    {
        $permission = $this->permissionRepository->findByIdOrFail(new PermissionId($query->permissionId));

        return $permission->toArray();
    }
}
