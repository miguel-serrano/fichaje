<?php
namespace App\DDD\User\Domain;
interface UserRepositoryInterface {
    public function save(User $user): User;
    public function findById(UserId $id): ?User;
    public function existsByEmail(Email $email): bool;
    /** @return User[] */
    public function findAll(): array;
    public function delete(UserId $id): bool;
}