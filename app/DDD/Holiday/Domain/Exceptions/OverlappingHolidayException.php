<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Exceptions;

use App\DDD\Holiday\Domain\ValueObjects\DateRange;

final class OverlappingHolidayException extends \Exception
{
    public function __construct(
        string $message = 'Ya tienes una solicitud de vacaciones que se solapa con estas fechas',
        int $code = 422,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function forDateRange(DateRange $dateRange): self
    {
        return new self(sprintf(
            'Ya tienes una solicitud de vacaciones que se solapa con el período %s - %s',
            $dateRange->startDateFormatted('d/m/Y'),
            $dateRange->endDateFormatted('d/m/Y')
        ));
    }
}
