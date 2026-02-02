<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Domain\Exception;

final class AccessDeniedException extends \RuntimeException
{
    public static function forAttribute(string $attribute, int $userId): self
    {
        return new self(
            sprintf('Permisos insuficientes para ejecutar el atributo %s al usuario.', $attribute)
        );
    }

    public static function withMessage(string $message): self
    {
        return new self($message);
    }
}
