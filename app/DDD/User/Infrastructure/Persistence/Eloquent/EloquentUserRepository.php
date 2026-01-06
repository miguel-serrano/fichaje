<?php

namespace App\DDD\User\Infrastructure\Persistence\Eloquent;

use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;
use Illuminate\Support\Facades\DB;

class EloquentUserRepository implements UserRepositoryInterface
{
    private function getUsersTable(): string
    {
        return 'users';
    }

    private function getRegistrosTable(): string
    {
        return 'time_entries';
    }

    public function save(User $user): User
    {
        $user_id = $user->id() ? $user->id()->value() : null;

        DB::transaction(function () use ($user, &$user_id) {
            $usersTable = $this->getUsersTable();
            $registrosTable = $this->getRegistrosTable();
            $now = now();

            $userData = [
                'uuid' => $user->uuid()->value(),
                'email' => $user->email()->value(),
                'name' => $user->name(),
                'is_active' => $user->isActive(),
                'updated_at' => $now,
            ];

            if ($user_id) {
                DB::table($usersTable)->where('id', $user_id)->update($userData);
            } else {
                $userData['created_at'] = $now;
                $user_id = DB::table($usersTable)->insertGetId($userData);
            }

            foreach ($user->timeEntries() as $entry) {
                $entryData = [
                    'user_id' => $user_id,
                    'entrada' => $entry->startTime()->format('Y-m-d H:i:s'),
                    'salida' => $entry->endTime() ? $entry->endTime()->format('Y-m-d H:i:s') : null,
                    'auto_closed' => $entry->isAutoClosed(),
                    'auto_close_reason' => $entry->autoCloseReason(),
                ];

                if ($entry->id()) {
                    DB::table($registrosTable)->where('id', $entry->id()->value())->update($entryData);
                } else {
                    $newId = DB::table($registrosTable)->insertGetId($entryData);
                    $entry->setId(new TimeEntryId($newId));
                }
            }
        });

        return $this->findById(new UserId($user_id));
    }

    public function findById(UserId $id): ?User
    {
        $eloquentUser = DB::table($this->getUsersTable())->where('id', $id->value())->first();

        if (! $eloquentUser) {
            return null;
        }

        $registrosHorarios = DB::table($this->getRegistrosTable())
            ->where('user_id', $eloquentUser->id)
            ->get()
            ->map(fn ($r) => (array) $r)
            ->toArray();

        return User::fromPrimitives(
            $eloquentUser->id,
            $eloquentUser->uuid,
            $eloquentUser->email,
            $eloquentUser->name,
            $eloquentUser->is_active ?? true,
            $eloquentUser->is_admin ?? false,
            $registrosHorarios
        );
    }

    public function findByIdOrFail(UserId $id): User
    {
        $user = $this->findById($id);

        if (! $user) {
            throw new UserNotFoundException("User {$id->value()} not found");
        }

        return $user;
    }

    public function findByUuid(Uuid $uuid): ?User
    {
        $eloquentUser = DB::table($this->getUsersTable())->where('uuid', $uuid->value())->first();

        if (! $eloquentUser) {
            return null;
        }

        $registrosHorarios = DB::table($this->getRegistrosTable())
            ->where('user_id', $eloquentUser->id)
            ->get()
            ->map(fn ($r) => (array) $r)
            ->toArray();

        return User::fromPrimitives(
            $eloquentUser->id,
            $eloquentUser->uuid,
            $eloquentUser->email,
            $eloquentUser->name,
            $eloquentUser->is_active ?? true,
            $eloquentUser->is_admin ?? false,
            $registrosHorarios
        );
    }

    public function findByUuidOrFail(Uuid $uuid): User
    {
        $user = $this->findByUuid($uuid);

        if (! $user) {
            throw new UserNotFoundException("User with UUID {$uuid->value()} not found");
        }

        return $user;
    }

    public function existsByEmail(Email $email): bool
    {
        return DB::table($this->getUsersTable())->where('email', $email->value())->exists();
    }

    /** @return User[] */
    public function findAll(): array
    {
        $users = DB::table($this->getUsersTable())->get();
        $allRegistros = DB::table($this->getRegistrosTable())->get()->groupBy('user_id');

        return $users->map(function ($user) use ($allRegistros) {
            $registros = $allRegistros->get($user->id, collect())
                ->map(fn ($r) => (array) $r)
                ->toArray();

            return User::fromPrimitives(
                $user->id,
                $user->uuid,
                $user->email,
                $user->name,
                $user->is_active ?? true,
                $user->is_admin ?? false,
                $registros
            );
        })->toArray();
    }

    public function delete(UserId $id): bool
    {
        return DB::transaction(function () use ($id) {
            DB::table($this->getRegistrosTable())->where('user_id', $id->value())->delete();

            return DB::table($this->getUsersTable())->where('id', $id->value())->delete() > 0;
        });
    }

    public function count(): int
    {
        return DB::table($this->getUsersTable())->count();
    }

    public function countTodayRegistrations(): int
    {
        return DB::table($this->getUsersTable())
            ->whereDate('created_at', today())
            ->count();
    }

    public function saveWithPassword(\App\DDD\User\Domain\Entity\User $user, \App\DDD\Authentication\Domain\ValueObjects\HashedPassword $password): User
    {
        $user_id = $user->id() ? $user->id()->value() : null;

        DB::transaction(function () use ($user, $password, &$user_id) {
            $usersTable = $this->getUsersTable();
            $registrosTable = $this->getRegistrosTable();
            $now = now();

            $userData = [
                'uuid' => $user->uuid()->value(),
                'email' => $user->email()->value(),
                'name' => $user->name(),
                'is_active' => $user->isActive(),
                'password' => $password->value(),
                'updated_at' => $now,
            ];

            if ($user_id) {
                DB::table($usersTable)->where('id', $user_id)->update($userData);
            } else {
                $userData['created_at'] = $now;
                $user_id = DB::table($usersTable)->insertGetId($userData);
            }

            foreach ($user->timeEntries() as $entry) {
                $entryData = [
                    'user_id' => $user_id,
                    'entrada' => $entry->startTime()->format('Y-m-d H:i:s'),
                    'salida' => $entry->endTime() ? $entry->endTime()->format('Y-m-d H:i:s') : null,
                    'auto_closed' => $entry->isAutoClosed(),
                    'auto_close_reason' => $entry->autoCloseReason(),
                ];

                if ($entry->id()) {
                    DB::table($registrosTable)->where('id', $entry->id()->value())->update($entryData);
                } else {
                    $newId = DB::table($registrosTable)->insertGetId($entryData);
                    $entry->setId(new TimeEntryId($newId));
                }
            }
        });

        return $this->findById(new UserId($user_id));
    }

    /** @return array<array-key, mixed> */
    public function findTodayTimeEntriesByUserId(UserId $id): array
    {
        return DB::table($this->getRegistrosTable())
            ->where('user_id', $id->value())
            ->whereDate('entrada', today())
            ->get()
            ->map(fn ($r) => (array) $r)
            ->toArray();
    }
}
