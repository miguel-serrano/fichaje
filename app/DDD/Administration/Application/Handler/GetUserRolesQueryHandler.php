<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Query\GetUserRolesQuery;
use App\DDD\Administration\Domain\Entity\Role;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;

class GetUserRolesQueryHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {
    }

    /**
     * @return array<array{id: int|null, name: string, slug: string, description: string|null, is_system: bool, hierarchy: int}>
     */
    public function handle(GetUserRolesQuery $query): array
    {
        return array_map(
            fn (Role $role) => $role->toArray(),
            $this->roleRepository->userRoles($query->userId)
        );
    }
}
