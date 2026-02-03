<?php

namespace App\DDD\Administration\Infrastructure\Persistence\Eloquent;

use App\DDD\Administration\Domain\Entity\Role;
use App\DDD\Administration\Domain\Exceptions\RoleNotFoundException;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\ValueObjects\RoleId;
use App\DDD\Administration\Domain\ValueObjects\RoleSlug;
use App\DDD\Administration\Infrastructure\Persistence\Eloquent\Builders\RoleQueryBuilder;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\Role as RoleModel;
use App\Models\RolePermission;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    private string $roleTable;

    private string $userRoleTable;

    private string $rolePermissionTable;

    public function __construct(
        private ConnectionInterface $connection,
    ) {
        $this->roleTable = RoleModel::tableName();
        $this->userRoleTable = UserRole::tableName();
        $this->rolePermissionTable = RolePermission::tableName();
    }

    public function userHasRole(UserId $userId, string $roleSlug): bool
    {
        return $this->query()
            ->join($this->userRoleTable, "{$this->roleTable}.id", '=', "{$this->userRoleTable}.role_id")
            ->where("{$this->userRoleTable}.user_id", $userId->value())
            ->where('slug', $roleSlug)
            ->exists();
    }

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
            $this->query()->whereRoleId($role->id())->update($data);
            $row = $this->query()->whereRoleId($role->id())->first();
        } else {
            $id = $this->query()->insertGetId($data);
            $row = $this->query()->where('id', $id)->first();
        }

        $permissions = $this->query()->permissionsForRole((int) $row->id);

        return $this->toDomainEntity($row, $permissions);
    }

    public function findById(RoleId $id): ?Role
    {
        $row = $this->query()->whereRoleId($id)->first();

        if (!$row) {
            return null;
        }

        $permissions = $this->query()->permissionsForRole($id->value());

        return $this->toDomainEntity($row, $permissions);
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
        $row = $this->query()->whereSlug($slug)->first();

        if (!$row) {
            return null;
        }

        $permissions = $this->query()->permissionsForRole((int) $row->id);

        return $this->toDomainEntity($row, $permissions);
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
        $rows = $this->query()->orderByHierarchy()->get();

        return $rows->map(function (\stdClass $row) {
            $permissions = $this->query()->permissionsForRole((int) $row->id);

            return $this->toDomainEntity($row, $permissions);
        })->toArray();
    }

    public function delete(RoleId $id): bool
    {
        $this->rolePermissionQuery()->where('role_id', $id->value())->delete();

        return $this->query()->whereRoleId($id)->delete() > 0;
    }

    /**
     * @param int[] $permissionIds
     */
    public function syncPermissions(RoleId $roleId, array $permissionIds): void
    {
        $uniquePermissionIds = array_unique($permissionIds);

        $this->connection->transaction(function () use ($roleId, $uniquePermissionIds) {
            $this->rolePermissionQuery()->where('role_id', $roleId->value())->delete();

            $inserts = array_map(fn (int $permissionId) => [
                'role_id' => $roleId->value(),
                'permission_id' => $permissionId,
            ], $uniquePermissionIds);

            if (!empty($inserts)) {
                $this->rolePermissionQuery()->insert($inserts);
            }
        });
    }

    public function assignRoleToUserBySystem(UserId $userId, RoleSlug $slug): void
    {
        $role = $this->findBySlugOrFail($slug);

        $now = time();

        $this->userRoleQuery()->updateOrInsert(
            ['user_id' => $userId->value(), 'role_id' => $role->id()->value()],
            ['created_at' => $now, 'updated_at' => $now]
        );
    }

    public function assignRole(UserId $userId, RoleId $roleId): void
    {
        $now = time();

        $this->userRoleQuery()->updateOrInsert(
            ['user_id' => $userId->value(), 'role_id' => $roleId->value()],
            ['created_at' => $now, 'updated_at' => $now]
        );
    }

    public function removeRole(UserId $userId, RoleId $roleId): void
    {
        $this->userRoleQuery()
            ->where('user_id', $userId->value())
            ->where('role_id', $roleId->value())
            ->delete();
    }

    /**
     * @return Role[]
     */
    public function userRoles(UserId $userId): array
    {
        $roleIds = $this->userRoleQuery()
            ->where('user_id', $userId->value())
            ->pluck('role_id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        if (empty($roleIds)) {
            return [];
        }

        return $this->query()
            ->whereIn('id', $roleIds)
            ->orderByHierarchy()
            ->get()
            ->map(function (\stdClass $row) {
                $permissions = $this->query()->permissionsForRole((int) $row->id);

                return $this->toDomainEntity($row, $permissions);
            })
            ->toArray();
    }

    private function query(): RoleQueryBuilder
    {
        $builder = new RoleQueryBuilder($this->connection);
        $builder->from($this->roleTable);

        return $builder;
    }

    private function userRoleQuery(): Builder
    {
        return $this->connection->table($this->userRoleTable);
    }

    private function rolePermissionQuery(): Builder
    {
        return $this->connection->table($this->rolePermissionTable);
    }

    /**
     * @param array<int, array<string, mixed>> $permissions
     */
    private function toDomainEntity(\stdClass $row, array $permissions): Role
    {
        return Role::fromPrimitives(
            $row->id,
            $row->name,
            $row->slug,
            $row->description,
            (bool) $row->is_system,
            $row->hierarchy,
            $permissions
        );
    }
}
