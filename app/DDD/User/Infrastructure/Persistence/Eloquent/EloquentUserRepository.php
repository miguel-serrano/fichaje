<?php

namespace App\DDD\User\Infrastructure\Persistence\Eloquent;

use App\DDD\Shared\Domain\ValueObject\UnixTimestamp;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UserDeletionFailedException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;
use App\Models\TimeEntry as TimeEntryModel;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\DB;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function save(User $user): User
    {
        $userId = $user->id()?->value();
        $now = UnixTimestamp::now()->value();

        DB::transaction(function () use ($user, &$userId, $now) {
            $data = [
                'uuid' => $user->uuid()->value(),
                'email' => $user->email()->value(),
                'name' => $user->name()->value(),
                'is_active' => $user->isActive(),
                'updated_at' => $now,
            ];

            if ($user->password()) {
                $data['password'] = $user->password()->value();
            }

            if ($userId) {
                $model = UserModel::find($userId);
                if (!$model) {
                    throw new UserNotFoundException("User {$userId} not found");
                }
                $model->update($data);
            } else {
                $data['created_at'] = $now;
                $model = UserModel::create($data);
                $userId = $model->id;
            }
        });

        return $this->findById(new UserId($userId));
    }

    public function findById(UserId $id): ?User
    {
        $model = UserModel::with('timeEntries')->find($id->value());

        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function findByIdOrFail(UserId $id): User
    {
        $user = $this->findById($id);

        if (!$user) {
            throw new UserNotFoundException("User {$id->value()} not found");
        }

        return $user;
    }

    public function findByUuid(Uuid $uuid): ?User
    {
        $model = UserModel::with('timeEntries')->where('uuid', $uuid->value())->first();

        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function findByUuidOrFail(Uuid $uuid): User
    {
        $user = $this->findByUuid($uuid);

        if (!$user) {
            throw new UserNotFoundException("User with UUID {$uuid->value()} not found");
        }

        return $user;
    }

    public function existsByEmail(Email $email): bool
    {
        return UserModel::where('email', $email->value())->exists();
    }

    /** @return User[] */
    public function findAll(): array
    {
        return UserModel::with('timeEntries')
            ->get()
            ->map(fn (UserModel $model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function delete(UserId $id): void
    {
        $deleted = DB::transaction(function () use ($id) {
            TimeEntryModel::where('user_id', $id->value())->delete();

            return UserModel::where('id', $id->value())->delete() > 0;
        });

        if (!$deleted) {
            throw UserDeletionFailedException::withId($id);
        }
    }

    public function count(): int
    {
        return UserModel::count();
    }

    public function countTodayRegistrations(): int
    {
        // Calcular rango del día actual en timestamps
        $todayStart = strtotime('today 00:00:00');
        $todayEnd = strtotime('today 23:59:59');

        return UserModel::where('created_at', '>=', $todayStart)
            ->where('created_at', '<=', $todayEnd)
            ->count();
    }

    /** @return array<array-key, mixed> */
    public function findTodayTimeEntriesByUserId(UserId $id): array
    {
        // Calcular rango del día actual en timestamps
        $todayStart = strtotime('today 00:00:00');
        $todayEnd = strtotime('today 23:59:59');

        return TimeEntryModel::where('user_id', $id->value())
            ->where('entrada', '>=', $todayStart)
            ->where('entrada', '<=', $todayEnd)
            ->get()
            ->toArray();
    }

    /** @return User[] */
    public function findAdmins(): array
    {
        return UserModel::with('timeEntries')
            ->whereHas('roles', fn ($query) => $query->whereIn('slug', ['super_admin', 'admin']))
            ->get()
            ->map(fn (UserModel $model) => $this->toDomainEntity($model))
            ->toArray();
    }

    /**
     * @return array{cerrados: \Illuminate\Support\Collection<int, \stdClass>, abiertos: \Illuminate\Support\Collection<int, \stdClass>}
     */
    public function findDailyTimeEntriesByUserId(UserId $id): array
    {
        $userId = $id->value();

        // Calcular rango del día actual en timestamps
        $todayStart = strtotime('today 00:00:00');
        $todayEnd = strtotime('today 23:59:59');

        return [
            'cerrados' => TimeEntryModel::where('user_id', $userId)
                ->whereNotNull('salida')
                ->orderBy('entrada', 'desc')
                ->get(),
            'abiertos' => TimeEntryModel::where('user_id', $userId)
                ->whereNull('salida')
                ->where('entrada', '>=', $todayStart)
                ->where('entrada', '<=', $todayEnd)
                ->orderBy('entrada', 'desc')
                ->get(),
        ];
    }

    private function toDomainEntity(UserModel $model): User
    {
        $timeEntries = $model->timeEntries->map(fn ($entry) => [
            'id' => $entry->id,
            'user_id' => $entry->user_id,
            'entrada' => $entry->entrada,
            'salida' => $entry->salida,
            'auto_closed' => $entry->auto_closed,
            'auto_close_reason' => $entry->auto_close_reason,
        ])->toArray();

        return User::fromPrimitives(
            $model->id,
            $model->uuid,
            $model->email,
            $model->name,
            $model->is_active ?? true,
            $timeEntries
        );
    }
}
