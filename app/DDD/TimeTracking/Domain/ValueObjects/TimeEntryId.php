<?php

namespace App\DDD\TimeTracking\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\IntValueObject;

final class TimeEntryId extends IntValueObject
{
    protected function validate(int $value): void
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('TimeEntryId must be a positive integer');
        }
    }
}
