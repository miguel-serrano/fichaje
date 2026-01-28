<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Exceptions;

final class UnauthorizedHolidayException extends \Exception
{
    public static function forRequest(): self
    {
        return new self('No tienes permisos para solicitar vacaciones');
    }

    public static function forViewOwn(): self
    {
        return new self('No tienes permisos para ver tus vacaciones');
    }

    public static function forViewPending(): self
    {
        return new self('No tienes permisos para ver las solicitudes pendientes');
    }

    public static function forViewApproved(): self
    {
        return new self('No tienes permisos para ver las vacaciones aprobadas');
    }

    public static function forApprove(): self
    {
        return new self('No tienes permisos para aprobar solicitudes de vacaciones');
    }

    public static function forReject(): self
    {
        return new self('No tienes permisos para rechazar solicitudes de vacaciones');
    }
}
