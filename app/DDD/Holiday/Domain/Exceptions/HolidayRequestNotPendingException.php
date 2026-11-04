<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Exceptions;

use App\DDD\Holiday\Domain\ValueObjects\HolidayStatus;

final class HolidayRequestNotPendingException extends \Exception
{
    public static function forStatus(HolidayStatus $status): self
    {
        return new self(sprintf(
            'La solicitud de vacaciones ya está %s y no se puede modificar',
            mb_strtolower($status->label())
        ), 409);
    }
}
