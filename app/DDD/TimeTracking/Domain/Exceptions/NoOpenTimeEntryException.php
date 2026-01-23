<?php

namespace App\DDD\TimeTracking\Domain\Exceptions;

class NoOpenTimeEntryException extends \Exception
{
    public function __construct(string $message = 'No hay una entrada de tiempo abierta para cerrar')
    {
        parent::__construct($message);
    }

    public static function forUser(string $userUuid): self
    {
        return new self("No hay entrada abierta para el usuario {$userUuid}");
    }
}
