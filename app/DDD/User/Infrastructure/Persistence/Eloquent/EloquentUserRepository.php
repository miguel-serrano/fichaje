<?php

namespace App\DDD\User\Infrastructure\Persistence\Eloquent;

use App\DDD\Authentication\Domain\ValueObjects\HashedPassword;
use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;
use App\Models\Role as RoleModel;
use App\Models\TimeEntry as TimeEntryModel;
use App\Models\User as UserModel;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

class EloquentUserRepository implements UserRepositoryInterface
{
    private string $usersTable;

    private string $timeEntriesTable;

    private string $userRoleTable;

    private string $rolesTable;

    public function __construct(
        private ConnectionInterface $connection,
    ) {
        $this->usersTable = UserModel::tableName();
        $this->timeEntriesTable = TimeEntryModel::tableName();
        $this->userRoleTable = UserRole::tableName();
        $this->rolesTable = RoleModel::tableName();
    }

    public function save(User $user): User
    {
        $userId = $user->id() ? $user->id()->value() : null;

        $this->connection->transaction(function () use ($user, &$userId) {
            $now = now();

            $userData = [
                'uuid' => $user->uuid()->value(),
                'email' => $user->email()->value(),
                'name' => $user->name(),
                'is_active' => $user->isActive(),
                'updated_at' => $now,
            ];

            if ($userId) {
                $this->usersQuery()->where('id', $userId)->update($userData);
            } else {
                $userData['created_at'] = $now;
                $userId = $this->usersQuery()->insertGetId($userData);
            }

            foreach ($user->timeEntries() as $entry) {
                $entryData = [
                    'user_id' => $userId,
                    'entrada' => $entry->startTime()->format('Y-m-d H:i:s'),
                    'salida' => $entry->endTime() ? $entry->endTime()->format('Y-m-d H:i:s') : null,
                    'auto_closed' => $entry->isAutoClosed(),
                    'auto_close_reason' => $entry->autoCloseReason(),
                ];

                if ($entry->id()) {
                    $this->timeEntriesQuery()->where('id', $entry->id()->value())->update($entryData);
                } else {
                    $newId = $this->timeEntriesQuery()->insertGetId($entryData);
                    $entry->setId(new TimeEntryId($newId));
                }
            }
        });

        return $this->findById(new UserId($userId));
    }

    public function findById(UserId $id): ?User
    {
        $row = $this->usersQuery()->where('id', $id->value())->first();

        if (!$row) {
            return null;
        }

        $timeEntries = $this->timeEntriesQuery()
            ->where('user_id', $row->id)
            ->get()
            ->map(fn (\stdClass $r) => (array) $r)
            ->toArray();

        return User::fromPrimitives(
            $row->id,
            $row->uuid,
            $row->email,
            $row->name,
            $row->is_active ?? true,
            $timeEntries
        );
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
        $row = $this->usersQuery()->where('uuid', $uuid->value())->first();

        if (!$row) {
            return null;
        }

        $timeEntries = $this->timeEntriesQuery()
            ->where('user_id', $row->id)
            ->get()
            ->map(fn (\stdClass $r) => (array) $r)
            ->toArray();

        return User::fromPrimitives(
            $row->id,
            $row->uuid,
            $row->email,
            $row->name,
            $row->is_active ?? true,
            $timeEntries
        );
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
        return $this->usersQuery()->where('email', $email->value())->exists();
    }

    /** @return User[] */
    public function findAll(): array
    {
        $rows = $this->usersQuery()->get();

        return $rows->map(fn (\stdClass $row) => User::fromPrimitives(
            $row->id,
            $row->uuid,
            $row->email,
            $row->name,
            $row->is_active ?? true
        ))->toArray();
    }

    public function delete(UserId $id): bool
    {
        return $this->connection->transaction(function () use ($id) {
            $this->timeEntriesQuery()->where('user_id', $id->value())->delete();

            return $this->usersQuery()->where('id', $id->value())->delete() > 0;
        });
    }

    public function count(): int
    {
        return $this->usersQuery()->count();
    }

    public function countTodayRegistrations(): int
    {
        return $this->usersQuery()
            ->whereDate('created_at', today())
            ->count();
    }

    public function saveWithPassword(User $user, HashedPassword $password): User
    {
        $userId = $user->id() ? $user->id()->value() : null;

        $this->connection->transaction(function () use ($user, $password, &$userId) {
            $now = now();

            $userData = [
                'uuid' => $user->uuid()->value(),
                'email' => $user->email()->value(),
                'name' => $user->name(),
                'is_active' => $user->isActive(),
                'password' => $password->value(),
                'updated_at' => $now,
            ];

            if ($userId) {
                $this->usersQuery()->where('id', $userId)->update($userData);
            } else {
                $userData['created_at'] = $now;
                $userId = $this->usersQuery()->insertGetId($userData);
            }

            foreach ($user->timeEntries() as $entry) {
                $entryData = [
                    'user_id' => $userId,
                    'entrada' => $entry->startTime()->format('Y-m-d H:i:s'),
                    'salida' => $entry->endTime() ? $entry->endTime()->format('Y-m-d H:i:s') : null,
                    'auto_closed' => $entry->isAutoClosed(),
                    'auto_close_reason' => $entry->autoCloseReason(),
                ];

                if ($entry->id()) {
                    $this->timeEntriesQuery()->where('id', $entry->id()->value())->update($entryData);
                } else {
                    $newId = $this->timeEntriesQuery()->insertGetId($entryData);
                    $entry->setId(new TimeEntryId($newId));
                }
            }
        });

        return $this->findById(new UserId($userId));
    }

    /** @return array<array-key, mixed> */
    public function findTodayTimeEntriesByUserId(UserId $id): array
    {
        return $this->timeEntriesQuery()
            ->where('user_id', $id->value())
            ->whereDate('entrada', today())
            ->get()
            ->map(fn (\stdClass $r) => (array) $r)
            ->toArray();
    }

    /** @return User[] */
    public function findAdmins(): array
    {
        $rows = $this->connection->table($this->usersTable)
            ->join($this->userRoleTable, "{$this->usersTable}.id", '=', "{$this->userRoleTable}.user_id")
            ->join($this->rolesTable, "{$this->userRoleTable}.role_id", '=', "{$this->rolesTable}.id")
            ->whereIn("{$this->rolesTable}.slug", ['super_admin', 'admin'])
            ->select("{$this->usersTable}.*")
            ->distinct()
            ->get();

        return $rows->map(fn (\stdClass $row) => User::fromPrimitives(
            $row->id,
            $row->uuid,
            $row->email,
            $row->name,
            $row->is_active ?? true
        ))->toArray();
    }

    private function usersQuery(): Builder
    {
        return $this->connection->table($this->usersTable);
    }

    private function timeEntriesQuery(): Builder
    {
        return $this->connection->table($this->timeEntriesTable);
    }
}
