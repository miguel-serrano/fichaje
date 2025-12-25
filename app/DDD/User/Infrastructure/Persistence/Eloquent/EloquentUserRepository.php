<?php
namespace App\DDD\User\Infrastructure\Persistence\Eloquent;
use App\DDD\User\Domain\Email;
use App\DDD\User\Domain\User;
use App\DDD\User\Domain\UserId;
use App\DDD\User\Domain\UserRepositoryInterface;
use App\Models\User as EloquentUser;
class EloquentUserRepository implements UserRepositoryInterface {
    public function save(User $user): User {
        $id = $user->id()->getValue();
        $eloquentUser = EloquentUser::updateOrCreate(
            ['id' => $id],
            [
                'id' => $id,
                'email' => $user->email()->getValue(),
                'name' => $user->name(),
                'is_active' => $user->isActive()
            ]
        );
        return User::fromPrimitives(
            $eloquentUser->id,
            $eloquentUser->email,
            $eloquentUser->name,
            $eloquentUser->is_active ?? true
        );
    }
    public function findById(UserId $id): ?User {
        $eloquentUser = EloquentUser::find($id->getValue());
        if (!$eloquentUser) return null;
        return User::fromPrimitives(
            $eloquentUser->id,
            $eloquentUser->email,
            $eloquentUser->name,
            $eloquentUser->is_active ?? true
        );
    }
    public function existsByEmail(Email $email): bool {
        return EloquentUser::where('email', $email->getValue())->exists();
    }
    /** @return User[] */
    public function findAll(): array {
        $eloquentUsers = EloquentUser::all();
        return $eloquentUsers->map(function ($eloquentUser) {
            return User::fromPrimitives(
                $eloquentUser->id,
                $eloquentUser->email,
                $eloquentUser->name,
                $eloquentUser->is_active ?? true
            );
        })->toArray();
    }
}