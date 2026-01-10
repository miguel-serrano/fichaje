<?php

namespace App\DDD\Authorization\Infrastructure\Persistence\Eloquent;

use App\DDD\Authorization\Domain\Entity\Role;
use App\DDD\Authorization\Domain\Exceptions\RoleNotFoundException;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\ValueObjects\RoleId;
use App\DDD\Authorization\Domain\ValueObjects\RoleSlug;
use App\Models\Role as RoleModel;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function save(Role $role): Role
    {
        $data = [
            'name' => $role->name()->value(),
            'slug' => $role->slug()->value(),
            'description' => $role->description(),
            'is_system' => $role->isSystem(),
            'hierarchy' => $role->hierarchy(),
        ];

        if ($role->id()) {
            RoleModel::where('id', $role->id()->value())->update($data);
            $model = RoleModel::find($role->id()->value());
        } else {
            $model = RoleModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(RoleId $id): ?Role
    {
        $model = RoleModel::with('permissions')->find($id->value());

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByIdOrFail(RoleId $id): Role
    {
        $role = $this->findById($id);

        if (!$role) {
            throw RoleNotFoundException::withId($id->value());
        }

        return $role;
    }

    public function findBySlug(RoleSlug $slug): ?Role
    {
        $model = RoleModel::with('permissions')->where('slug', $slug->value())->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySlugOrFail(RoleSlug $slug): Role
    {
        $role = $this->findBySlug($slug);

        if (!$role) {
            throw RoleNotFoundException::withSlug($slug->value());
        }

        return $role;
    }

    /**
     * @return Role[]
     */
    public function findAll(): array
    {
        return RoleModel::with('permissions')
            ->orderBy('hierarchy', 'desc')
            ->get()
            ->map(fn (RoleModel $model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function delete(RoleId $id): bool
    {
        return RoleModel::destroy($id->value()) > 0;
    }

    /**
     * @param int[] $permissionIds
     */
    public function syncPermissions(RoleId $roleId, array $permissionIds): void
    {
        $model = RoleModel::find($roleId->value());
        if ($model) {
            $model->permissions()->sync($permissionIds);
        }
    }

    private function toDomainEntity(RoleModel $model): Role
    {
        $permissions = $model->permissions->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'slug' => $permission->slug,
                'bounded_context' => $permission->bounded_context,
                'description' => $permission->description,
                'is_system' => $permission->is_system,
            ];
        })->toArray();

        return Role::fromPrimitives(
            $model->id,
            $model->name,
            $model->slug,
            $model->description,
            $model->is_system,
            $model->hierarchy,
            $permissions
        );
    }
}
