<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Domain\Exception;

final class AccessDeniedException extends \RuntimeException
{
    public static function forAttribute(string $attribute, int $userId): self
    {
        return new self(
            sprintf('Access denied for user <%d> on attribute <%s>', $userId, $attribute)
        );
    }

    public static function withMessage(string $message): self
    {
        return new self($message);
    }
}
