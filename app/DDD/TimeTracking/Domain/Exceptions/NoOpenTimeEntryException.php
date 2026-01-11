<?php

namespace App\DDD\TimeTracking\Domain\Exceptions;

class NoOpenTimeEntryException extends \Exception
{
    public function __construct()
    {
        parent::__construct('No open time entry exists to close.');
    }
}
