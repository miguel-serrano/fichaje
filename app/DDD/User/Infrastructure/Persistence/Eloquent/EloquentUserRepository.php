<?php

namespace App\DDD\User\Infrastructure\Persistence\Eloquent;

use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
use App\DDD\User\Domain\Entity\User;
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
        $user_id = $user->id() ? $user->id()->getValue() : null;

        DB::transaction(function () use ($user, &$user_id) {
            $usersTable = $this->getUsersTable();
            $registrosTable = $this->getRegistrosTable();
            $now = now();

            $userData = [
                'uuid' => $user->uuid()->getValue(),
                'email' => $user->email()->getValue(),
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

            foreach ($user->registrosHorarios() as $registro) {
                $registroData = [
                    'user_id' => $user_id,
                    'entrada' => $registro->entrada()->format('Y-m-d H:i:s'),
                    'salida' => $registro->salida() ? $registro->salida()->format('Y-m-d H:i:s') : null,
                ];

                if ($registro->id()) {
                    DB::table($registrosTable)->where('id', $registro->id()->getValue())->update($registroData);
                } else {
                    $newId = DB::table($registrosTable)->insertGetId($registroData);
                    $registro->setId(new TimeEntryId($newId));
                }
            }
        });

        return $this->findById(new UserId($user_id));
    }

    public function findById(UserId $id): ?User
    {
        $eloquentUser = DB::table($this->getUsersTable())->where('id', $id->getValue())->first();

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
            $eloquentUser->remember_token ?? null,
            $registrosHorarios
        );
    }

    public function findByUuid(Uuid $uuid): ?User
    {
        $eloquentUser = DB::table($this->getUsersTable())->where('uuid', $uuid->getValue())->first();

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
            $eloquentUser->remember_token ?? null,
            $registrosHorarios
        );
    }

    public function existsByEmail(Email $email): bool
    {
        return DB::table($this->getUsersTable())->where('email', $email->getValue())->exists();
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
                $user->remember_token ?? null,
                $registros
            );
        })->toArray();
    }

    public function delete(UserId $id): bool
    {
        return DB::transaction(function () use ($id) {
            DB::table($this->getRegistrosTable())->where('user_id', $id->getValue())->delete();

            return DB::table($this->getUsersTable())->where('id', $id->getValue())->delete() > 0;
        });
    }

    public function count(): int
    {
        return DB::table($this->getUsersTable())->count();
    }

    public function saveWithPassword(\App\DDD\User\Domain\Entity\User $user, \App\DDD\Authentication\Domain\ValueObjects\HashedPassword $password): User
    {
        $user_id = $user->id() ? $user->id()->getValue() : null;

        DB::transaction(function () use ($user, $password, &$user_id) {
            $usersTable = $this->getUsersTable();
            $registrosTable = $this->getRegistrosTable();
            $now = now();

            $userData = [
                'uuid' => $user->uuid()->getValue(),
                'email' => $user->email()->getValue(),
                'name' => $user->name(),
                'is_active' => $user->isActive(),
                'password' => $password->getValue(),
                'updated_at' => $now,
            ];

            if ($user_id) {
                DB::table($usersTable)->where('id', $user_id)->update($userData);
            } else {
                $userData['created_at'] = $now;
                $user_id = DB::table($usersTable)->insertGetId($userData);
            }

            foreach ($user->registrosHorarios() as $registro) {
                $registroData = [
                    'user_id' => $user_id,
                    'entrada' => $registro->entrada()->format('Y-m-d H:i:s'),
                    'salida' => $registro->salida() ? $registro->salida()->format('Y-m-d H:i:s') : null,
                ];

                if ($registro->id()) {
                    DB::table($registrosTable)->where('id', $registro->id()->getValue())->update($registroData);
                } else {
                    $newId = DB::table($registrosTable)->insertGetId($registroData);
                    $registro->setId(new TimeEntryId($newId));
                }
            }
        });

        return $this->findById(new UserId($user_id));
    }
}
