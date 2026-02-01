<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Query\GetUserRolesQuery;
use App\DDD\Administration\Domain\Entity\Role;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;

class GetUserRolesQueryHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private ConnectionInterface $connection,
    ) {
    }

    /**
     * @return array<array{id: int|null, name: string, slug: string, description: string|null, is_system: bool, hierarchy: int}>
     */
    public function handle(GetUserRolesQuery $query): array
    {
        $roleIds = $this->connection->table(UserRole::tableName())
            ->where('user_id', $query->userId->value())
            ->pluck('role_id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        if (empty($roleIds)) {
            return [];
        }

        return collect($this->roleRepository->findAll())
            ->filter(fn (Role $role) => in_array($role->id()?->value(), $roleIds, true))
            ->map(fn (Role $role) => [
                'id' => $role->id()?->value(),
                'name' => $role->name()->value(),
                'slug' => $role->slug()->value(),
                'description' => $role->description(),
                'is_system' => $role->isSystem(),
                'hierarchy' => $role->hierarchy(),
            ])
            ->values()
            ->toArray();
    }
}
