<?php

namespace App\DDD\TimeTracking\Domain\Exceptions;

class NoOpenTimeEntryException extends \Exception
{
    public function __construct(string $message = 'No open time entry exists to close.')
    {
        parent::__construct($message);
    }

    public static function forUser(string $userUuid): self
    {
        return new self("No open time entry exists for user {$userUuid}");
    }
}
