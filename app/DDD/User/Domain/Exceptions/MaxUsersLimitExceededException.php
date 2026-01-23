<?php

namespace App\DDD\User\Domain\Exceptions;

class MaxUsersLimitExceededException extends \Exception
{
    public function __construct(string $message = 'Se ha excedido el límite máximo de usuarios')
    {
        parent::__construct($message);
    }
}
