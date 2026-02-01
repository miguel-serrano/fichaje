<?php

declare(strict_types=1);

namespace App\DDD\Shared\Domain\ValueObject;

use MichaelRubel\ValueObjects\ValueObject;

/**
 * @template T
 * @template TKey of array-key
 * @template TValue
 *
 * @extends ValueObject<TKey, TValue>
 */
abstract class CollectionValueObject extends ValueObject
{
    /** @var T[] */
    protected array $items;

    /**
     * @param T[] $items
     */
    public function __construct(array $items)
    {
        if (isset($this->items)) {
            throw new \InvalidArgumentException(static::IMMUTABLE_MESSAGE);
        }

        $this->items = array_values($items);

        $this->validateItems();
        $this->validate();
    }

    /**
     * @return T[]
     */
    public function value(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * @return array<int, mixed>
     */
    abstract public function toPrimitives(): array;

    abstract protected function itemType(): string;

    protected function validate(): void
    {
    }

    private function validateItems(): void
    {
        $expectedType = $this->itemType();

        foreach ($this->items as $index => $item) {
            if (!$item instanceof $expectedType) {
                throw new \InvalidArgumentException(sprintf('El elemento en la posición %d debe ser una instancia de %s', $index, $expectedType));
            }
        }
    }
}
