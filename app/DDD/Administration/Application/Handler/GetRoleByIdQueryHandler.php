<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Query\GetRoleByIdQuery;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\ValueObjects\RoleId;

class GetRoleByIdQueryHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {}

    /**
     * @return array{id: int|null, name: string, slug: string, description: string|null, is_system: bool, hierarchy: int, permissions: array}
     */
    public function handle(GetRoleByIdQuery $query): array
    {
        $role = $this->roleRepository->findByIdOrFail(new RoleId($query->roleId));

        return $role->toArray();
    }
}
