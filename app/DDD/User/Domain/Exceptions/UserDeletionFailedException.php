<?php

namespace App\DDD\User\Domain\Exceptions;

use App\DDD\User\Domain\ValueObjects\UserId;

class UserDeletionFailedException extends \Exception
{
    public static function withId(UserId $userId): self
    {
        return new self("Failed to delete user with ID {$userId->value()}");
    }
}
