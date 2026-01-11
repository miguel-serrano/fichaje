<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Exceptions;

use Exception;

class InvalidHolidayDateRangeException extends Exception
{
    public function __construct(
        string $message = 'Rango de fechas de vacaciones inválido',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function endDateBeforeStartDate(): self
    {
        return new self('La fecha de fin debe ser posterior a la fecha de inicio');
    }

    public static function startDateInPast(): self
    {
        return new self('La fecha de inicio no puede ser anterior a hoy');
    }
}
