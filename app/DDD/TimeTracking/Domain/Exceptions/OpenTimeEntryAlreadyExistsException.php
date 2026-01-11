<?php

namespace App\DDD\TimeTracking\Domain\Exceptions;

class OpenTimeEntryAlreadyExistsException extends \Exception
{
    public function __construct()
    {
        parent::__construct('An open time entry already exists.');
    }
}
