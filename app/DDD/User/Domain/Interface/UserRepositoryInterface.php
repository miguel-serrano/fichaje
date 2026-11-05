<?php

namespace App\DDD\User\Domain\Interface;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UserDeletionFailedException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;

/**
 * Salvo que el método diga lo contrario, los usuarios se devuelven con sus roles pero
 * SIN fichajes (timeEntries() vacío): cargar el historial completo en cada lectura no escala.
 */
interface UserRepositoryInterface
{
    public function save(User $user): User;

    public function findById(UserId $id): ?User;

    /**
     * @throws UserNotFoundException
     */
    public function findByIdOrFail(UserId $id): User;

    /**
     * Igual que findByIdOrFail() pero con TODO el historial de fichajes (ficha de usuario).
     *
     * @throws UserNotFoundException
     */
    public function findByIdWithTimeEntriesOrFail(UserId $id): User;

    /**
     * Carga varios usuarios en una sola consulta (sin fichajes). Los ids inexistentes se ignoran.
     *
     * @param UserId[] $ids
     *
     * @return array<int, User> indexado por id
     */
    public function findByIds(array $ids): array;

    public function findByUuid(Uuid $uuid): ?User;

    /**
     * @throws UserNotFoundException
     */
    public function findByUuidOrFail(Uuid $uuid): User;

    public function existsByEmail(Email $email): bool;

    /** @return User[] */
    public function findAll(): array;

    /**
     * @throws UserDeletionFailedException
     */
    public function delete(UserId $id): void;

    public function count(): int;

    public function countTodayRegistrations(): int;

    /** @return array<array-key, mixed> */
    public function findTodayTimeEntriesByUserId(UserId $id): array;

    /** @return User[] */
    public function findByRole(string $roleSlug): array;

    public function userHasRole(UserId $id, string $roleSlug): bool;

    /**
     * @return array{cerrados: \Illuminate\Support\Collection<int, \stdClass>, abiertos: \Illuminate\Support\Collection<int, \stdClass>}
     */
    public function findDailyTimeEntriesByUserId(UserId $id): array;
}
