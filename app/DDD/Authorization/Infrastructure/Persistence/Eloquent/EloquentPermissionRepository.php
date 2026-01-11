<?php

namespace App\DDD\Authorization\Infrastructure\Persistence\Eloquent;

use App\DDD\Authorization\Domain\Entity\Permission;
use App\DDD\Authorization\Domain\Exceptions\PermissionNotFoundException;
use App\DDD\Authorization\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Authorization\Domain\ValueObjects\BoundedContext;
use App\DDD\Authorization\Domain\ValueObjects\PermissionId;
use App\DDD\Authorization\Domain\ValueObjects\PermissionSlug;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\Permission as PermissionModel;
use App\Models\RolePermission;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

class EloquentPermissionRepository implements PermissionRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {
    }

    public function userHasPermission(UserId $userId, string $permissionSlug): bool
    {
        $userRoleTable = UserRole::tableName();
        $rolePermissionTable = RolePermission::tableName();
        $permissionTable = PermissionModel::tableName();

        $query = $this->connection->table($userRoleTable)
            ->join($rolePermissionTable, "{$userRoleTable}.role_id", '=', "{$rolePermissionTable}.role_id")
            ->join($permissionTable, "{$rolePermissionTable}.permission_id", '=', "{$permissionTable}.id")
            ->where("{$userRoleTable}.user_id", $userId->value())
            ->where("{$permissionTable}.slug", $permissionSlug);

        return $query->exists();
    }

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
            $this->query()->where('id', $permission->id()->value())->update($data);
            $row = $this->query()->where('id', $permission->id()->value())->first();
        } else {
            $id = $this->query()->insertGetId($data);
            $row = $this->query()->where('id', $id)->first();
        }

        return $this->toDomainEntity($row);
    }

    public function findById(PermissionId $id): ?Permission
    {
        $row = $this->query()->where('id', $id->value())->first();

        return $row ? $this->toDomainEntity($row) : null;
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
        $row = $this->query()->where('slug', $slug->value())->first();

        return $row ? $this->toDomainEntity($row) : null;
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
        $rows = $this->query()
            ->orderBy('bounded_context')
            ->orderBy('slug')
            ->get();

        return $rows->map(fn (\stdClass $row) => $this->toDomainEntity($row))->toArray();
    }

    /**
     * @return Permission[]
     */
    public function findByBoundedContext(BoundedContext $context): array
    {
        $rows = $this->query()
            ->where('bounded_context', $context->value)
            ->orderBy('slug')
            ->get();

        return $rows->map(fn (\stdClass $row) => $this->toDomainEntity($row))->toArray();
    }

    public function delete(PermissionId $id): bool
    {
        return $this->query()->where('id', $id->value())->delete() > 0;
    }

    private function query(): Builder
    {
        return $this->connection->table(PermissionModel::tableName());
    }

    private function toDomainEntity(\stdClass $row): Permission
    {
        return Permission::fromPrimitives(
            $row->id,
            $row->name,
            $row->slug,
            $row->bounded_context,
            $row->description,
            (bool) $row->is_system
        );
    }
}
