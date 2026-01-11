<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Exceptions;

class HolidayRequestNotFoundException extends \Exception
{
    public function __construct(
        string $message = 'Solicitud de vacaciones no encontrada',
        int $code = 404,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function withId(int|string $id): self
    {
        return new self("Solicitud de vacaciones con ID {$id} no encontrada");
    }
}
