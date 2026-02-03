<?php

namespace App\DDD\Administration\Infrastructure\Persistence\Eloquent\Builders;

use App\DDD\Administration\Domain\ValueObjects\RoleId;
use App\DDD\Administration\Domain\ValueObjects\RoleSlug;
use App\Models\Permission as PermissionModel;
use App\Models\RolePermission;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;

class RoleQueryBuilder extends Builder
{
    private string $rolePermissionTable;

    private string $permissionTable;

    public function __construct(
        ConnectionInterface $connection,
        ?Grammar $grammar = null,
        ?Processor $processor = null,
    ) {
        parent::__construct($connection, $grammar, $processor);
        $this->rolePermissionTable = RolePermission::tableName();
        $this->permissionTable = PermissionModel::tableName();
    }

    public function whereRoleId(RoleId $id): self
    {
        return $this->where('id', $id->value());
    }

    public function whereSlug(RoleSlug $slug): self
    {
        return $this->where('slug', $slug->value());
    }

    public function orderByHierarchy(string $direction = 'desc'): self
    {
        return $this->orderBy('hierarchy', $direction);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function permissionsForRole(int $roleId): array
    {
        return $this->connection->table($this->rolePermissionTable)
            ->join(
                $this->permissionTable,
                "{$this->rolePermissionTable}.permission_id",
                '=',
                "{$this->permissionTable}.id"
            )
            ->where("{$this->rolePermissionTable}.role_id", $roleId)
            ->select("{$this->permissionTable}.*")
            ->get()
            ->map(fn (\stdClass $row) => [
                'id' => $row->id,
                'name' => $row->name,
                'slug' => $row->slug,
                'bounded_context' => $row->bounded_context,
                'description' => $row->description,
                'is_system' => (bool) $row->is_system,
            ])->toArray();
    }
}
