<?php
namespace App\DDD\User\Domain\Interface;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;

interface UserRepositoryInterface {
    public function save(User $user): User;
    public function findById(UserId $id): ?User;
    public function findByUuid(Uuid $uuid): ?User;
    public function existsByEmail(Email $email): bool;
    /** @return User[] */
    public function findAll(): array;
    public function delete(UserId $id): bool;
}