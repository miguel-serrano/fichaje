<?php

namespace App\DDD\TimeTracking\Domain\Exceptions;

use Exception;

class UnsavedUserCannotClockInException extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot clock in for an unsaved user.');
    }
}
