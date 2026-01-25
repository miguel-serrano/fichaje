<?php

namespace App\DDD\Authorization\Infrastructure\Persistence\Eloquent;

use App\DDD\Authorization\Domain\Entity\Role;
use App\DDD\Authorization\Domain\Exceptions\RoleNotFoundException;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\ValueObjects\RoleId;
use App\DDD\Authorization\Domain\ValueObjects\RoleSlug;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\Permission as PermissionModel;
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

    private string $permissionTable;

    public function __construct(
        private ConnectionInterface $connection,
    ) {
        $this->roleTable = RoleModel::tableName();
        $this->userRoleTable = UserRole::tableName();
        $this->rolePermissionTable = RolePermission::tableName();
        $this->permissionTable = PermissionModel::tableName();
    }

    public function userHasRole(UserId $userId, string $roleSlug): bool
    {
        $query = $this->connection->table($this->userRoleTable)
            ->join($this->roleTable, "{$this->userRoleTable}.role_id", '=', "{$this->roleTable}.id")
            ->where("{$this->userRoleTable}.user_id", $userId->value())
            ->where("{$this->roleTable}.slug", $roleSlug);

        return $query->exists();
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
            $this->query()->where('id', $role->id()->value())->update($data);
            $row = $this->query()->where('id', $role->id()->value())->first();
        } else {
            $id = $this->query()->insertGetId($data);
            $row = $this->query()->where('id', $id)->first();
        }

        $permissions = $this->getPermissionsForRole((int) $row->id);

        return $this->toDomainEntity($row, $permissions);
    }

    public function findById(RoleId $id): ?Role
    {
        $row = $this->query()->where('id', $id->value())->first();

        if (!$row) {
            return null;
        }

        $permissions = $this->getPermissionsForRole($id->value());

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
        $row = $this->query()->where('slug', $slug->value())->first();

        if (!$row) {
            return null;
        }

        $permissions = $this->getPermissionsForRole((int) $row->id);

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
        $rows = $this->query()
            ->orderBy('hierarchy', 'desc')
            ->get();

        return $rows->map(function (\stdClass $row) {
            $permissions = $this->getPermissionsForRole((int) $row->id);

            return $this->toDomainEntity($row, $permissions);
        })->toArray();
    }

    public function delete(RoleId $id): bool
    {
        $this->rolePermissionQuery()->where('role_id', $id->value())->delete();

        return $this->query()->where('id', $id->value())->delete() > 0;
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

        $this->connection->table($this->userRoleTable)->updateOrInsert(
            ['user_id' => $userId->value(), 'role_id' => $role->id()->value()],
            ['created_at' => $now, 'updated_at' => $now]
        );
    }

    private function query(): Builder
    {
        return $this->connection->table($this->roleTable);
    }

    private function rolePermissionQuery(): Builder
    {
        return $this->connection->table($this->rolePermissionTable);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getPermissionsForRole(int $roleId): array
    {
        $rows = $this->connection->table($this->rolePermissionTable)
            ->join($this->permissionTable, "{$this->rolePermissionTable}.permission_id", '=', "{$this->permissionTable}.id")
            ->where("{$this->rolePermissionTable}.role_id", $roleId)
            ->select("{$this->permissionTable}.*")
            ->get();

        return $rows->map(fn (\stdClass $row) => [
            'id' => $row->id,
            'name' => $row->name,
            'slug' => $row->slug,
            'bounded_context' => $row->bounded_context,
            'description' => $row->description,
            'is_system' => (bool) $row->is_system,
        ])->toArray();
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
