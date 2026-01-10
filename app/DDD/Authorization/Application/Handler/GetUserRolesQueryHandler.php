<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Query\GetUserRolesQuery;
use App\DDD\Authorization\Domain\Entity\Role;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use Illuminate\Support\Facades\DB;

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
        $roleIds = DB::table('user_role')
            ->where('user_id', $query->userId)
            ->pluck('role_id')
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
