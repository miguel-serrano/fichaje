<?php

namespace App\DDD\User\Domain\Interface;

use App\DDD\Authentication\Domain\ValueObjects\HashedPassword;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;

interface UserRepositoryInterface
{
    public function save(User $user): User;

    public function saveWithPassword(User $user, HashedPassword $password): User;

    public function findById(UserId $id): ?User;

    /**
     * @throws \App\DDD\User\Domain\Exceptions\UserNotFoundException
     */
    public function findByIdOrFail(UserId $id): User;

    public function findByUuid(Uuid $uuid): ?User;

    /**
     * @throws \App\DDD\User\Domain\Exceptions\UserNotFoundException
     */
    public function findByUuidOrFail(Uuid $uuid): User;

    public function existsByEmail(Email $email): bool;

    /** @return User[] */
    public function findAll(): array;

    /**
     * @throws \App\DDD\User\Domain\Exceptions\UserDeletionFailedException
     */
    public function delete(UserId $id): void;

    public function count(): int;

    public function countTodayRegistrations(): int;

    /** @return array<array-key, mixed> */
    public function findTodayTimeEntriesByUserId(UserId $id): array;

    /** @return User[] */
    public function findAdmins(): array;

    /**
     * @return array{cerrados: \Illuminate\Support\Collection<int, \stdClass>, abiertos: \Illuminate\Support\Collection<int, \stdClass>}
     */
    public function findDailyTimeEntriesByUserId(UserId $id): array;
}
