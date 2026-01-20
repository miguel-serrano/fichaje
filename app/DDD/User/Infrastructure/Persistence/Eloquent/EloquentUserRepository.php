<?php

namespace App\DDD\User\Infrastructure\Persistence\Eloquent;

use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
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

        DB::transaction(function () use ($user, &$userId) {
            $data = [
                'uuid' => $user->uuid()->value(),
                'email' => $user->email()->value(),
                'name' => $user->name()->value(),
                'is_active' => $user->isActive(),
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
                $model = UserModel::create($data);
                $userId = $model->id;
            }

            foreach ($user->timeEntries() as $entry) {
                $entryData = [
                    'user_id' => $userId,
                    'entrada' => $entry->startTime()->format('Y-m-d H:i:s'),
                    'salida' => $entry->endTime()?->format('Y-m-d H:i:s'),
                    'auto_closed' => $entry->isAutoClosed(),
                    'auto_close_reason' => $entry->autoCloseReason(),
                ];

                if ($entry->id()) {
                    TimeEntryModel::where('id', $entry->id()->value())->update($entryData);
                } else {
                    $newEntry = TimeEntryModel::create($entryData);
                    $entry->setId(new TimeEntryId($newEntry->id));
                }
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
        return UserModel::whereDate('created_at', today())->count();
    }

    /** @return array<array-key, mixed> */
    public function findTodayTimeEntriesByUserId(UserId $id): array
    {
        return TimeEntryModel::where('user_id', $id->value())
            ->whereDate('entrada', today())
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

        return [
            'cerrados' => TimeEntryModel::where('user_id', $userId)
                ->whereNotNull('salida')
                ->orderBy('entrada', 'desc')
                ->get(),
            'abiertos' => TimeEntryModel::where('user_id', $userId)
                ->whereNull('salida')
                ->whereDate('entrada', today())
                ->orderBy('entrada', 'desc')
                ->get(),
        ];
    }

    private function toDomainEntity(UserModel $model): User
    {
        $timeEntries = $model->timeEntries->map(fn ($entry) => [
            'id' => $entry->id,
            'user_id' => $entry->user_id,
            'entrada' => $entry->entrada->format('Y-m-d H:i:s'),
            'salida' => $entry->salida?->format('Y-m-d H:i:s'),
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
