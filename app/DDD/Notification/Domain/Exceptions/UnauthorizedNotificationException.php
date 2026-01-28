<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain\Exceptions;

final class UnauthorizedNotificationException extends \Exception
{
    public static function forViewOwn(): self
    {
        return new self('No tienes permisos para ver tus notificaciones');
    }

    public static function forViewAll(): self
    {
        return new self('No tienes permisos para ver todas las notificaciones');
    }

    public static function forSend(): self
    {
        return new self('No tienes permisos para enviar notificaciones');
    }

    public static function forMarkAsRead(): self
    {
        return new self('No tienes permisos para marcar notificaciones como leídas');
    }

    public static function forDelete(): self
    {
        return new self('No tienes permisos para eliminar notificaciones');
    }
}
