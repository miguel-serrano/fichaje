<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Interface;

use App\DDD\User\Domain\Entity\User;

interface ClockOutValidatorInterface
{
    /**
     * Valida que el usuario puede fichar salida.
     *
     * @throws \App\DDD\TimeTracking\Domain\Exceptions\NoOpenTimeEntryException si no tiene una entrada abierta
     */
    public function ensureCanClockOut(User $user): void;
}
