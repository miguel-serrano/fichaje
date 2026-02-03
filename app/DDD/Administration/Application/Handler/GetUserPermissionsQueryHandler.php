<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Query\GetUserPermissionsQuery;
use App\DDD\Administration\Domain\Interface\PermissionRepositoryInterface;

class GetUserPermissionsQueryHandler
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
    ) {
    }

    /**
     * @return string[]
     */
    public function handle(GetUserPermissionsQuery $query): array
    {
        return $this->permissionRepository->userPermissions($query->userId);
    }
}
