<?php

namespace App\DDD\Administration\Infrastructure\Persistence\Eloquent;

use App\DDD\Administration\Domain\Entity\Permission;
use App\DDD\Administration\Domain\Exceptions\PermissionNotFoundException;
use App\DDD\Administration\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Administration\Domain\ValueObjects\BoundedContext;
use App\DDD\Administration\Domain\ValueObjects\PermissionId;
use App\DDD\Administration\Domain\ValueObjects\PermissionSlug;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\Permission as PermissionModel;
use App\Models\Role as RoleModel;
use App\Models\RolePermission;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

class EloquentPermissionRepository implements PermissionRepositoryInterface
{
    private string $permissionTable;

    private string $userRoleTable;

    private string $rolePermissionTable;

    private string $roleTable;

    public function __construct(
        private ConnectionInterface $connection,
    ) {
        $this->permissionTable = PermissionModel::tableName();
        $this->userRoleTable = UserRole::tableName();
        $this->rolePermissionTable = RolePermission::tableName();
        $this->roleTable = RoleModel::tableName();
    }

    public function userHasPermission(UserId $userId, string $permissionSlug): bool
    {
        $query = $this->connection->table($this->userRoleTable)
            ->join($this->rolePermissionTable, "{$this->userRoleTable}.role_id", '=', "{$this->rolePermissionTable}.role_id")
            ->join($this->permissionTable, "{$this->rolePermissionTable}.permission_id", '=', "{$this->permissionTable}.id")
            ->where("{$this->userRoleTable}.user_id", $userId->value())
            ->where("{$this->permissionTable}.slug", $permissionSlug);

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

    /**
     * @return string[]
     */
    public function userPermissions(UserId $userId): array
    {
        $hasSuperAdmin = $this->connection->table($this->userRoleTable)
            ->join($this->roleTable, "{$this->userRoleTable}.role_id", '=', "{$this->roleTable}.id")
            ->where("{$this->userRoleTable}.user_id", $userId->value())
            ->where("{$this->roleTable}.slug", 'super_admin')
            ->exists();

        if ($hasSuperAdmin) {
            return $this->query()
                ->pluck('slug')
                ->toArray();
        }

        return $this->connection->table($this->userRoleTable)
            ->join($this->rolePermissionTable, "{$this->userRoleTable}.role_id", '=', "{$this->rolePermissionTable}.role_id")
            ->join($this->permissionTable, "{$this->rolePermissionTable}.permission_id", '=', "{$this->permissionTable}.id")
            ->where("{$this->userRoleTable}.user_id", $userId->value())
            ->distinct()
            ->pluck("{$this->permissionTable}.slug")
            ->toArray();
    }

    private function query(): Builder
    {
        return $this->connection->table($this->permissionTable);
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
