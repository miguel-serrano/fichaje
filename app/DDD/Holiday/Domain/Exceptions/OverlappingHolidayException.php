<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Exceptions;

use Exception;

class OverlappingHolidayException extends Exception
{
    public function __construct(
        string $message = 'Ya tienes una solicitud de vacaciones que se solapa con estas fechas',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
