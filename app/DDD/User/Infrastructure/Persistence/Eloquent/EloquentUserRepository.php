<?php
namespace App\DDD\User\Infrastructure\Persistence\Eloquent;
use App\DDD\User\Domain\Email;
use App\DDD\User\Domain\User;
use App\DDD\User\Domain\UserId;
use App\DDD\User\Domain\UserRepositoryInterface;
use App\Models\User as EloquentUser;
use Illuminate\Support\Facades\DB;
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
        $users = DB::select('SELECT id, email, name, is_active FROM users');
        dd($users);
        return array_map(function ($user) {
            return User::fromPrimitives(
                $user->id,
                $user->email,
                $user->name,
                $user->is_active ?? true
            );
        }, $users);
    }
}