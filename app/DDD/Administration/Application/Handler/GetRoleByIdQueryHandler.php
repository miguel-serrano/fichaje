<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Query\GetRoleByIdQuery;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;

class GetRoleByIdQueryHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {
    }

    /**
     * @return array{id: int|null, name: string, slug: string, description: string|null, is_system: bool, hierarchy: int, permissions: array}
     */
    public function handle(GetRoleByIdQuery $query): array
    {
        $role = $this->roleRepository->findByIdOrFail($query->roleId);

        return $role->toArray();
    }
}
