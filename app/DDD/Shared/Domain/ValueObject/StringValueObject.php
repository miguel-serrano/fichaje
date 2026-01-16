<?php

namespace App\DDD\Shared\Domain\ValueObject;

use Illuminate\Support\Stringable;
use MichaelRubel\ValueObjects\ValueObject;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @method static static make(string|Stringable $value)
 * @method static static from(string|Stringable $value)
 * @method static static makeOrNull(string|Stringable|null $value)
 *
 * @extends ValueObject<TKey, TValue>
 */
abstract class StringValueObject extends ValueObject
{
    protected string $value;

    public function __construct(string|Stringable $value)
    {
        if (isset($this->value)) {
            throw new \InvalidArgumentException(static::IMMUTABLE_MESSAGE);
        }

        $this->value = (string) $value;

        $this->validate();
    }

    public function value(): string
    {
        return $this->value;
    }

    protected function validate(): void {}
}
