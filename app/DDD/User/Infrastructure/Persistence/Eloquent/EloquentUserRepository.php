<?php
namespace App\DDD\User\Infrastructure\Persistence\Eloquent;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\Models\User as EloquentUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;


class EloquentUserRepository implements UserRepositoryInterface {
    private function getTableName(): string {
        return app()->runningUnitTests() ? 'users_tests' : 'users';
    }
    
    public function save(User $user): User {
        $tableName = $this->getTableName();
        $now = now();
        
        // Busca por uuid, NO por id.
        $existing = DB::table($tableName)->where('uuid', $user->uuid()->getValue())->first();
        
        if ($existing) {
            DB::table($tableName)->where('uuid', $user->uuid()->getValue())->update([
                'email' => $user->email()->getValue(),
                'name' => $user->name(),
                'is_active' => $user->isActive(),
                'updated_at' => $now
            ]);
        } else {
            DB::table($tableName)->insert([
                'uuid' => $user->uuid()->getValue(),
                'email' => $user->email()->getValue(),
                'name' => $user->name(),
                'is_active' => $user->isActive(),
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
        
        $savedUser = DB::table($tableName)->where('uuid', $user->uuid()->getValue())->first();
        return User::fromPrimitives(
            $savedUser->id,
            $savedUser->uuid,
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
            $eloquentUser->uuid,
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
        $users = DB::table($tableName)->select('id', 'uuid', 'email', 'name', 'is_active')->get();
        
        return $users->map(function ($user) {
            return User::fromPrimitives(
                $user->id,
                $user->uuid,
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