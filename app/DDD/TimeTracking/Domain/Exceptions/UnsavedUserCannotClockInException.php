<?php

namespace App\DDD\TimeTracking\Domain\Exceptions;

class UnsavedUserCannotClockInException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Cannot clock in for an unsaved user.');
    }
}
