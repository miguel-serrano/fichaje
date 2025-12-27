<?php
namespace App\DDD\User\Domain;

use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
interface UserRepositoryInterface {
    public function save(User $user): User;
    public function findById(UserId $id): ?User;
    public function existsByEmail(Email $email): bool;
    /** @return User[] */
    public function findAll(): array;
    public function delete(UserId $id): bool;
}