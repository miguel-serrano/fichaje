<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Interface;

use App\DDD\User\Domain\Entity\User;

interface ClockInValidatorInterface
{
    /**
     * Valida que el usuario puede fichar entrada.
     *
     * @throws \App\DDD\TimeTracking\Domain\Exceptions\OpenTimeEntryAlreadyExistsException si ya tiene una entrada abierta
     * @throws \App\DDD\TimeTracking\Domain\Exceptions\DailyLimitExceededException         si excede el límite diario
     */
    public function ensureCanClockIn(User $user): void;
}
