<?php

namespace App\DDD\User\Domain\Exceptions;

class DailyUserRegistrationLimitExceededException extends \Exception
{
    public const MAX_DAILY_REGISTRATIONS = 8;

    public function __construct(int $currentCount)
    {
        parent::__construct(
            'Se ha alcanzado el límite máximo de '.self::MAX_DAILY_REGISTRATIONS." registros de usuarios por día. Registros de hoy: {$currentCount}."
        );
    }
}
