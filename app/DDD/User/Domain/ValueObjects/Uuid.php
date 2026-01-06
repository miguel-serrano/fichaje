<?php

namespace App\DDD\User\Domain\ValueObjects;

use Illuminate\Support\Str;
use MichaelRubel\ValueObjects\Collection\Complex\Uuid as BaseUuid;

/**
 * @method static static make(string $value)
 * @method static static from(string $value)
 * @method static static makeOrNull(string|null $value)
 */
final class Uuid extends BaseUuid
{
    public static function generate(): self
    {
        return new self(Str::orderedUuid()->toString());
    }
}
