<?php
namespace App\DDD\User\Infrastructure\Persistence\Eloquent;
use App\DDD\User\Domain\Email;
use App\DDD\User\Domain\User;
use App\DDD\User\Domain\UserId;
use App\DDD\User\Domain\UserRepositoryInterface;
use App\Models\User as EloquentUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class EloquentUserRepository implements UserRepositoryInterface {
    private function getTableName(): string {
        // Use users_tests when running tests or when APP_ENV is testing
        if (App::runningUnitTests() || App::environment('testing')) {
            return 'users_tests';
        }
        return 'users';
    }
    public function save(User $user): User {
        $id = $user->id()->getValue();
        $tableName = $this->getTableName();
        
        $existing = DB::table($tableName)->where('id', $id)->first();
        $now = now();
        
        if ($existing) {
            DB::table($tableName)->where('id', $id)->update([
                'email' => $user->email()->getValue(),
                'name' => $user->name(),
                'is_active' => $user->isActive(),
                'updated_at' => $now
            ]);
        } else {
            DB::table($tableName)->insert([
                'id' => $id,
                'email' => $user->email()->getValue(),
                'name' => $user->name(),
                'is_active' => $user->isActive(),
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
        
        $savedUser = DB::table($tableName)->where('id', $id)->first();
        return User::fromPrimitives(
            $savedUser->id,
            $savedUser->email,
            $savedUser->name,
            $savedUser->is_active ?? true
        );
    }


    public function findById(UserId $id): ?User {
        $tableName = $this->getTableName();
        $eloquentUser = DB::table($tableName)->where('id', $id->getValue())->first();
        if (!$eloquentUser) return null;
        return User::fromPrimitives(
            $eloquentUser->id,
            $eloquentUser->email,
            $eloquentUser->name,
            $eloquentUser->is_active ?? true
        );
    }

    public function existsByEmail(Email $email): bool {
        $tableName = $this->getTableName();
        return DB::table($tableName)->where('email', $email->getValue())->exists();
    }

    /** @return User[] */
    public function findAll(): array {
        $tableName = $this->getTableName();
        $users = DB::table($tableName)->select('id', 'email', 'name', 'is_active')->get();
        
        return $users->map(function ($user) {
            return User::fromPrimitives(
                $user->id,
                $user->email,
                $user->name,
                $user->is_active ?? true
            );
        })->toArray();
    }

    public function delete(UserId $id): bool {
        $tableName = $this->getTableName();
        return DB::table($tableName)->where('id', $id->getValue())->delete() > 0;
    }
}