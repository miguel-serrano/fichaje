<?php

namespace App\DDD\Administration\Infrastructure\Persistence\Eloquent;

use App\DDD\Administration\Domain\Entity\Permission;
use App\DDD\Administration\Domain\Exceptions\PermissionNotFoundException;
use App\DDD\Administration\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Administration\Domain\ValueObjects\BoundedContext;
use App\DDD\Administration\Domain\ValueObjects\Description;
use App\DDD\Administration\Domain\ValueObjects\PermissionId;
use App\DDD\Administration\Domain\ValueObjects\PermissionName;
use App\DDD\Administration\Domain\ValueObjects\PermissionSlug;
use App\DDD\Administration\Infrastructure\Persistence\Eloquent\Builders\PermissionQueryBuilder;
use App\DDD\Shared\Domain\ValueObject\UnixTimestamp;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\Permission as PermissionModel;
use App\Models\RolePermission;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;

class EloquentPermissionRepository implements PermissionRepositoryInterface
{
    private string $permissionTable;

    private string $userRoleTable;

    private string $rolePermissionTable;

    public function __construct(
        private ConnectionInterface $connection,
    ) {
        $this->permissionTable = PermissionModel::tableName();
        $this->userRoleTable = UserRole::tableName();
        $this->rolePermissionTable = RolePermission::tableName();
    }

    public function userHasPermission(UserId $userId, string $permissionSlug): bool
    {
        return $this->query()
            ->join($this->rolePermissionTable, "{$this->permissionTable}.id", '=', "{$this->rolePermissionTable}.permission_id")
            ->join($this->userRoleTable, "{$this->rolePermissionTable}.role_id", '=', "{$this->userRoleTable}.role_id")
            ->where("{$this->userRoleTable}.user_id", $userId->value())
            ->where('slug', $permissionSlug)
            ->exists();
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
            $this->query()->wherePermissionId($permission->id())->update($data);
            $row = $this->query()->wherePermissionId($permission->id())->first();
        } else {
            $id = $this->query()->insertGetId($data);
            $row = $this->query()->where('id', $id)->first();
        }

        return $this->toDomainEntity($row);
    }

    public function update(PermissionId $id, PermissionName $name, ?Description $description): Permission
    {
        $this->query()->wherePermissionId($id)->update([
            'name' => $name->value(),
            'description' => $description?->value(),
            'updated_at' => UnixTimestamp::now()->value(),
        ]);

        return $this->findByIdOrFail($id);
    }

    public function findById(PermissionId $id): ?Permission
    {
        $row = $this->query()->wherePermissionId($id)->first();

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
        $row = $this->query()->whereSlug($slug)->first();

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

    public function findAll(): array
    {
        $rows = $this->query()->orderByDefault()->get();

        return $rows->map(fn (\stdClass $row) => $this->toDomainEntity($row))->toArray();
    }

    public function findByBoundedContext(BoundedContext $context): array
    {
        $rows = $this->query()
            ->whereBoundedContext($context)
            ->orderBy('slug')
            ->get();

        return $rows->map(fn (\stdClass $row) => $this->toDomainEntity($row))->toArray();
    }

    public function delete(PermissionId $id): bool
    {
        return $this->query()->wherePermissionId($id)->delete() > 0;
    }

    public function userPermissions(UserId $userId): array
    {
        return $this->query()
            ->join($this->rolePermissionTable, "{$this->permissionTable}.id", '=', "{$this->rolePermissionTable}.permission_id")
            ->join($this->userRoleTable, "{$this->rolePermissionTable}.role_id", '=', "{$this->userRoleTable}.role_id")
            ->where("{$this->userRoleTable}.user_id", $userId->value())
            ->distinct()
            ->pluck('slug')
            ->toArray();
    }

    private function query(): PermissionQueryBuilder
    {
        $builder = new PermissionQueryBuilder($this->connection);
        $builder->from($this->permissionTable);

        return $builder;
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
