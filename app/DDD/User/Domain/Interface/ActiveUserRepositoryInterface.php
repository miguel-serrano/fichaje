<?php

declare(strict_types=1);

namespace App\DDD\User\Domain\Interface;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\Uuid;

interface ActiveUserRepositoryInterface
{
    /**
     * Busca un usuario activo por UUID o lanza excepción.
     *
     * @throws \App\DDD\User\Domain\Exceptions\UserNotFoundException  si el usuario no existe
     * @throws \App\DDD\User\Domain\Exceptions\UserNotActiveException si el usuario no está activo
     */
    public function findActiveByUuidOrFail(Uuid $uuid): User;
}
