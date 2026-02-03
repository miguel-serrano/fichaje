<?php

declare(strict_types=1);

namespace App\DDD\Administration\Domain\Event;

use App\DDD\Shared\Domain\Event\AbstractDomainEvent;

final class RoleUpdatedEvent extends AbstractDomainEvent
{
    /**
     * @param array{added?: string[], removed?: string[]}|null $permissionsSynced
     */
    public function __construct(
        string $aggregateId,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?array $permissionsSynced = null,
    ) {
        parent::__construct($aggregateId);
    }

    public static function eventName(): string
    {
        return 'role.updated';
    }

    /** @return array{name: string, slug: string, permissions_synced: array{added?: string[], removed?: string[]}|null} */
    public function toPrimitives(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'permissions_synced' => $this->permissionsSynced,
        ];
    }
}
