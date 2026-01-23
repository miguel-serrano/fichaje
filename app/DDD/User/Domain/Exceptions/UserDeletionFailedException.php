<?php

namespace App\DDD\User\Domain\Exceptions;

use App\DDD\User\Domain\ValueObjects\UserId;

class UserDeletionFailedException extends \Exception
{
    public static function withId(UserId $userId): self
    {
        return new self("Error al eliminar el usuario con ID {$userId->value()}");
    }
}
