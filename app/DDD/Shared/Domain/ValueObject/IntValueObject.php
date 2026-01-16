<?php

namespace App\DDD\Shared\Domain\ValueObject;

use MichaelRubel\ValueObjects\ValueObject;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @method static static make(int $value)
 * @method static static from(int $value)
 * @method static static makeOrNull(int|null $value)
 *
 * @extends ValueObject<TKey, TValue>
 */
abstract class IntValueObject extends ValueObject
{
    protected int $value;

    public function __construct(int $value)
    {
        if (isset($this->value)) {
            throw new \InvalidArgumentException(static::IMMUTABLE_MESSAGE);
        }

        $this->value = $value;

        $this->validate();
    }

    public function value(): int
    {
        return $this->value;
    }

    protected function validate(): void {}
}
