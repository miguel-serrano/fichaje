<?php

namespace App\DDD\Authorization\Infrastructure\Persistence\Eloquent;

use App\DDD\Authorization\Domain\Entity\Permission;
use App\DDD\Authorization\Domain\Exceptions\PermissionNotFoundException;
use App\DDD\Authorization\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Authorization\Domain\ValueObjects\BoundedContext;
use App\DDD\Authorization\Domain\ValueObjects\PermissionId;
use App\DDD\Authorization\Domain\ValueObjects\PermissionSlug;
use App\Models\Permission as PermissionModel;

class EloquentPermissionRepository implements PermissionRepositoryInterface
{
    public function save(Permission $permission): Permission
    {
        $data = [
            'name' => $permission->name()->value(),
            'slug' => $permission->slug()->value(),
            'bounded_context' => $permission->boundedContext()->value,
            'description' => $permission->description(),
            'is_system' => $permission->isSystem(),
        ];

        if ($permission->id()) {
            PermissionModel::where('id', $permission->id()->value())->update($data);
            $model = PermissionModel::find($permission->id()->value());
        } else {
            $model = PermissionModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(PermissionId $id): ?Permission
    {
        $model = PermissionModel::find($id->value());

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByIdOrFail(PermissionId $id): Permission
    {
        $permission = $this->findById($id);

        if (!$permission) {
            throw PermissionNotFoundException::withId($id->value());
        }

        return $permission;
    }

    public function findBySlug(PermissionSlug $slug): ?Permission
    {
        $model = PermissionModel::where('slug', $slug->value())->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySlugOrFail(PermissionSlug $slug): Permission
    {
        $permission = $this->findBySlug($slug);

        if (!$permission) {
            throw PermissionNotFoundException::withSlug($slug->value());
        }

        return $permission;
    }

    /**
     * @return Permission[]
     */
    public function findAll(): array
    {
        return PermissionModel::orderBy('bounded_context')
            ->orderBy('slug')
            ->get()
            ->map(fn (PermissionModel $model) => $this->toDomainEntity($model))
            ->toArray();
    }

    /**
     * @return Permission[]
     */
    public function findByBoundedContext(BoundedContext $context): array
    {
        return PermissionModel::where('bounded_context', $context->value)
            ->orderBy('slug')
            ->get()
            ->map(fn (PermissionModel $model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function delete(PermissionId $id): bool
    {
        return PermissionModel::destroy($id->value()) > 0;
    }

    private function toDomainEntity(PermissionModel $model): Permission
    {
        return Permission::fromPrimitives(
            $model->id,
            $model->name,
            $model->slug,
            $model->bounded_context,
            $model->description,
            $model->is_system
        );
    }
}
