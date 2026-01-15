<?php

namespace App\DDD\TimeTracking\Domain\Exceptions;

class OpenTimeEntryAlreadyExistsException extends \Exception
{
    public function __construct(string $message = 'An open time entry already exists.')
    {
        parent::__construct($message);
    }

    public static function forUser(string $userUuid): self
    {
        return new self("An open time entry already exists for user {$userUuid}");
    }
}
