<?php

declare(strict_types=1);

namespace App\DDD\Administration\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\CollectionValueObject;

/**
 * @extends CollectionValueObject<PermissionId>
 */
final class PermissionIdCollection extends CollectionValueObject
{
    /**
     * @param int[] $ids
     */
    public static function fromPrimitives(array $ids): self
    {
        return new self(
            array_map(fn (int $id): PermissionId => PermissionId::make($id), $ids)
        );
    }

    /**
     * @return int[]
     */
    public function toPrimitives(): array
    {
        return array_map(fn (PermissionId $id): int => $id->value(), $this->items);
    }

    protected function itemType(): string
    {
        return PermissionId::class;
    }
}
