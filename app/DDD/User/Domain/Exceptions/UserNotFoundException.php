<?php

namespace App\DDD\User\Domain\Exceptions;

use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;

class UserNotFoundException extends \Exception
{
    public function __construct(string $message = 'Usuario no encontrado', int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function byId(UserId $id): self
    {
        return new self("Usuario con ID {$id->value()} no encontrado");
    }

    public static function byUuid(Uuid $uuid): self
    {
        return new self("Usuario con UUID {$uuid->value()} no encontrado");
    }

    public static function byEmail(string $email): self
    {
        return new self("Usuario con email {$email} no encontrado");
    }
}
