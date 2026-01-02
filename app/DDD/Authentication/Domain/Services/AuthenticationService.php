<?php

namespace App\DDD\Authentication\Domain\Services;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\Email;

interface AuthenticationService
{
    public function attempt(Email $email, string $password): bool;

    public function login(User $user): void;

    public function logout(): void;

    public function user(): ?User;

    public function check(): bool;
}
