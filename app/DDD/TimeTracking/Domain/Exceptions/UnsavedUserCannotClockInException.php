<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Exceptions;

final class UnsavedUserCannotClockInException extends \Exception
{
    public function __construct(string $message = 'No se puede fichar entrada para un usuario no guardado.')
    {
        parent::__construct($message);
    }

    public static function create(): self
    {
        return new self();
    }
}
