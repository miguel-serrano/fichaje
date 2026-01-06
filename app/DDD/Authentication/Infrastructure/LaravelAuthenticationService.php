<?php

namespace App\DDD\Authentication\Infrastructure;

use App\DDD\Authentication\Domain\Services\AuthenticationService;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Support\Facades\Auth;

final class LaravelAuthenticationService implements AuthenticationService
{
    public function __construct(
        private EloquentUserRepository $userRepository
    ) {}

    public function attempt(Email $email, string $password): bool
    {
        return Auth::attempt([
            'email' => $email->value(),
            'password' => $password,
        ]);
    }

    public function login(User $user): void
    {
        $eloquentUser = \App\Models\User::find($user->id()->value());
        Auth::login($eloquentUser);
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function user(): ?User
    {
        $eloquentUser = Auth::user();

        if (! $eloquentUser) {
            return null;
        }

        return $this->userRepository->findById(
            new UserId($eloquentUser->id)
        );
    }

    public function check(): bool
    {
        return Auth::check();
    }
}
