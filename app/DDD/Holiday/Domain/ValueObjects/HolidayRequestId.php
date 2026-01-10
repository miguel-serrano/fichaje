<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;
use InvalidArgumentException;

final class HolidayRequestId extends StringValueObject
{
    public function __construct(string|int $value)
    {
        parent::__construct((string) $value);
    }

    protected function validate(): void
    {
        if (empty($this->value)) {
            throw new InvalidArgumentException('Holiday Request ID cannot be empty');
        }
    }
}
