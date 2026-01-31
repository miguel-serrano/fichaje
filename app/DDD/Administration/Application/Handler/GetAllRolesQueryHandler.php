<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Query\GetAllRolesQuery;
use App\DDD\Administration\Domain\Entity\Role;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;

class GetAllRolesQueryHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {}

    /**
     * @return array<array{id: int|null, name: string, slug: string, description: string|null, is_system: bool, hierarchy: int, permissions: array}>
     */
    public function handle(GetAllRolesQuery $query): array
    {
        return array_map(
            fn (Role $role) => $role->toArray(),
            $this->roleRepository->findAll()
        );
    }
}
