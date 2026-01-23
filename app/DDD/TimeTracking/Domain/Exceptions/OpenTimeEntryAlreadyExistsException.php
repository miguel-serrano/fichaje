<?php

namespace App\DDD\TimeTracking\Domain\Exceptions;

class OpenTimeEntryAlreadyExistsException extends \Exception
{
    public function __construct(string $message = 'Ya existe una entrada de tiempo abierta')
    {
        parent::__construct($message);
    }

    public static function forUser(string $userUuid): self
    {
        return new self("Ya existe una entrada abierta para el usuario {$userUuid}");
    }
}
