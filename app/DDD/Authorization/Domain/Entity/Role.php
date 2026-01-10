<?php

namespace App\DDD\Authorization\Domain\Entity;

use App\DDD\Authorization\Domain\ValueObjects\PermissionSlug;
use App\DDD\Authorization\Domain\ValueObjects\RoleId;
use App\DDD\Authorization\Domain\ValueObjects\RoleName;
use App\DDD\Authorization\Domain\ValueObjects\RoleSlug;

final class Role
{
    /** @var Permission[] */
    private array $permissions = [];

    private function __construct(
        private ?RoleId $id,
        private RoleName $name,
        private RoleSlug $slug,
        private ?string $description,
        private bool $isSystem,
        private int $hierarchy,
    ) {
    }

    public static function create(
        RoleName $name,
        RoleSlug $slug,
        ?string $description = null,
        bool $isSystem = false,
        int $hierarchy = 0,
    ): self {
        return new self(null, $name, $slug, $description, $isSystem, $hierarchy);
    }

    /**
     * @param array<array{id: int|null, name: string, slug: string, bounded_context: string, description: string|null, is_system: bool}> $permissions
     */
    public static function fromPrimitives(
        ?int $id,
        string $name,
        string $slug,
        ?string $description,
        bool $isSystem,
        int $hierarchy,
        array $permissions = [],
    ): self {
        $role = new self(
            null !== $id ? new RoleId($id) : null,
            new RoleName($name),
            new RoleSlug($slug),
            $description,
            $isSystem,
            $hierarchy
        );

        foreach ($permissions as $permission) {
            $role->addPermission(Permission::fromPrimitives(
                $permission['id'],
                $permission['name'],
                $permission['slug'],
                $permission['bounded_context'],
                $permission['description'] ?? null,
                $permission['is_system'] ?? false
            ));
        }

        return $role;
    }

    public function addPermission(Permission $permission): void
    {
        $this->permissions[] = $permission;
    }

    public function hasPermission(PermissionSlug $slug): bool
    {
        foreach ($this->permissions as $permission) {
            if ($permission->slug()->equals($slug)) {
                return true;
            }
        }

        return false;
    }

    public function clearPermissions(): void
    {
        $this->permissions = [];
    }

    public function id(): ?RoleId
    {
        return $this->id;
    }

    public function name(): RoleName
    {
        return $this->name;
    }

    public function slug(): RoleSlug
    {
        return $this->slug;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function hierarchy(): int
    {
        return $this->hierarchy;
    }

    /**
     * @return Permission[]
     */
    public function permissions(): array
    {
        return $this->permissions;
    }

    /**
     * @return array{id: int|null, name: string, slug: string, description: string|null, is_system: bool, hierarchy: int, permissions: array<array{id: int|null, name: string, slug: string, bounded_context: string, description: string|null, is_system: bool}>}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id?->value(),
            'name' => $this->name->value(),
            'slug' => $this->slug->value(),
            'description' => $this->description,
            'is_system' => $this->isSystem,
            'hierarchy' => $this->hierarchy,
            'permissions' => array_map(fn (Permission $p) => $p->toArray(), $this->permissions),
        ];
    }
}
