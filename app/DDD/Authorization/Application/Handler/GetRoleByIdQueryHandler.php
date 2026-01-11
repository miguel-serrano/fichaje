<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Query\GetRoleByIdQuery;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\ValueObjects\RoleId;

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
        $role = $this->roleRepository->findByIdOrFail(new RoleId($query->roleId));

        return $role->toArray();
    }
}
