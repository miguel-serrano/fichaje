<?php

namespace App\DDD\Administration\Domain\Entity;

use App\DDD\Administration\Domain\Exceptions\CannotDeleteSystemPermissionException;
use App\DDD\Administration\Domain\ValueObjects\BoundedContext;
use App\DDD\Administration\Domain\ValueObjects\PermissionId;
use App\DDD\Administration\Domain\ValueObjects\PermissionName;
use App\DDD\Administration\Domain\ValueObjects\PermissionSlug;

final class Permission
{
    private function __construct(
        private ?PermissionId $id,
        private PermissionName $name,
        private PermissionSlug $slug,
        private BoundedContext $boundedContext,
        private ?string $description,
        private bool $isSystem,
    ) {
    }

    public static function create(
        PermissionName $name,
        PermissionSlug $slug,
        BoundedContext $boundedContext,
        ?string $description = null,
        bool $isSystem = false,
    ): self {
        return new self(null, $name, $slug, $boundedContext, $description, $isSystem);
    }

    public static function fromPrimitives(
        ?int $id,
        string $name,
        string $slug,
        string $boundedContext,
        ?string $description,
        bool $isSystem,
    ): self {
        return new self(
            null !== $id ? new PermissionId($id) : null,
            new PermissionName($name),
            new PermissionSlug($slug),
            BoundedContext::from($boundedContext),
            $description,
            $isSystem
        );
    }

    public function id(): ?PermissionId
    {
        return $this->id;
    }

    public function name(): PermissionName
    {
        return $this->name;
    }

    public function slug(): PermissionSlug
    {
        return $this->slug;
    }

    public function boundedContext(): BoundedContext
    {
        return $this->boundedContext;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    /**
     * @throws CannotDeleteSystemPermissionException
     */
    public function assertCanDelete(): void
    {
        if ($this->isSystem) {
            throw CannotDeleteSystemPermissionException::forPermission($this->slug->value());
        }
    }

    /**
     * @return array{id: int|null, name: string, slug: string, bounded_context: string, description: string|null, is_system: bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id?->value(),
            'name' => $this->name->value(),
            'slug' => $this->slug->value(),
            'bounded_context' => $this->boundedContext->value,
            'description' => $this->description,
            'is_system' => $this->isSystem,
        ];
    }
}
