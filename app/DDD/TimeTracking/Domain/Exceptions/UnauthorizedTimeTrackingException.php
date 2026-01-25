<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Exceptions;

class UnauthorizedTimeTrackingException extends \Exception
{
    public function __construct(
        string $message = 'No tienes permisos para realizar esta acción de registro horario',
        int $code = 403,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function forClockIn(): self
    {
        return new self('No tienes permisos para registrar entrada para este usuario');
    }

    public static function forClockOut(): self
    {
        return new self('No tienes permisos para registrar salida para este usuario');
    }

    public static function forView(): self
    {
        return new self('No tienes permisos para ver los registros de este usuario');
    }

    public static function forCloseOrphanEntries(): self
    {
        return new self('No tienes permisos para cerrar registros huérfanos');
    }

    public static function forReports(): self
    {
        return new self('No tienes permisos para ver los informes de registro horario');
    }
}
