<?php

namespace App\DDD\Authentication\Infrastructure\Service;

use App\DDD\Authentication\Domain\Services\AuthenticationService;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use App\Models\User as UserModel;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\ConnectionInterface;

final class LaravelAuthenticationService implements AuthenticationService
{
    private string $userTable;

    public function __construct(
        private StatefulGuard $guard,
        private EloquentUserRepository $userRepository,
        private ConnectionInterface $connection,
    ) {
        $this->userTable = UserModel::tableName();
    }

    public function attempt(Email $email, string $password): bool
    {
        return $this->guard->attempt([
            'email' => $email->value(),
            'password' => $password,
        ]);
    }

    public function login(User $user): void
    {
        $eloquentUser = $this->findEloquentUser($user->id()->value());
        if ($eloquentUser) {
            $this->guard->login($eloquentUser);
        }
    }

    public function logout(): void
    {
        $this->guard->logout();
    }

    public function user(): ?User
    {
        $eloquentUser = $this->guard->user();

        if (! $eloquentUser) {
            return null;
        }

        return $this->userRepository->findById(
            new UserId($eloquentUser->id)
        );
    }

    public function check(): bool
    {
        return $this->guard->check();
    }

    private function findEloquentUser(int $id): ?UserModel
    {
        $row = $this->connection->table($this->userTable)->where('id', $id)->first();

        if (! $row) {
            return null;
        }

        $user = new UserModel;
        $user->forceFill((array) $row);
        $user->exists = true;

        return $user;
    }
}
