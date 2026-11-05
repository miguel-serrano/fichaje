<?php

declare(strict_types=1);

namespace App\DDD\User\Domain\Interface;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UserNotActiveException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\ValueObjects\Uuid;

interface ActiveUserRepositoryInterface
{
    /**
     * Busca un usuario activo por UUID o lanza excepción.
     *
     * Para fichar: timeEntries() contiene solo la "ventana de fichaje"
     * (entradas abiertas de cualquier día + entradas que empezaron hoy).
     *
     * @throws UserNotFoundException  si el usuario no existe
     * @throws UserNotActiveException si el usuario no está activo
     */
    public function findActiveByUuidOrFail(Uuid $uuid): User;
}
