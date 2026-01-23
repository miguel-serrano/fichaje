<?php

namespace App\DDD\User\Domain\Exceptions;

use App\DDD\User\Domain\ValueObjects\Uuid;

class UserNotActiveException extends \Exception
{
    public function __construct(?Uuid $uuid = null)
    {
        $message = $uuid
            ? "El usuario con UUID {$uuid->value()} no está activo"
            : 'Tu cuenta está pendiente de activación';

        parent::__construct($message, 403);
    }

    public static function forCurrentUser(): self
    {
        return new self(null);
    }

    public static function forUuid(Uuid $uuid): self
    {
        return new self($uuid);
    }
}
