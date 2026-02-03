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
use App\Models\Role as RoleModel;
use App\Models\TimeEntry as TimeEntryModel;
use App\Models\User as UserModel;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

class EloquentUserRepository implements UserRepositoryInterface
{
    protected string $userTable;

    protected string $timeEntryTable;

    protected string $userRoleTable;

    protected string $roleTable;

    public function __construct(protected ConnectionInterface $connection)
    {
        $this->userTable = UserModel::tableName();
        $this->timeEntryTable = TimeEntryModel::tableName();
        $this->userRoleTable = UserRole::tableName();
        $this->roleTable = RoleModel::tableName();
    }

    public function save(User $user): User
    {
        $userId = $user->id()?->value();
        $now = UnixTimestamp::now()->value();

        $this->connection->transaction(function () use ($user, &$userId, $now) {
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
                $exists = $this->query()->where('id', $userId)->exists();
                if (!$exists) {
                    throw new UserNotFoundException("User {$userId} not found");
                }
                $this->query()->where('id', $userId)->update($data);
            } else {
                $data['created_at'] = $now;
                $userId = $this->query()->insertGetId($data);
            }
        });

        return $this->findById(new UserId($userId));
    }

    public function findById(UserId $id): ?User
    {
        $row = $this->query()->where('id', $id->value())->first();

        if (!$row) {
            return null;
        }

        $timeEntries = $this->getTimeEntriesForUser($id->value());
        $roleSlugs = $this->getRoleSlugsForUser($id->value());

        return $this->toDomainEntity($row, $timeEntries, $roleSlugs);
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
        $row = $this->query()->where('uuid', $uuid->value())->first();

        if (!$row) {
            return null;
        }

        $timeEntries = $this->getTimeEntriesForUser((int) $row->id);
        $roleSlugs = $this->getRoleSlugsForUser((int) $row->id);

        return $this->toDomainEntity($row, $timeEntries, $roleSlugs);
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
        return $this->query()->where('email', $email->value())->exists();
    }

    /** @return User[] */
    public function findAll(): array
    {
        $rows = $this->query()->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $userIds = $rows->pluck('id')->toArray();
        $allTimeEntries = $this->getTimeEntriesForUsers($userIds);
        $allRoleSlugs = $this->getRoleSlugsForUsers($userIds);

        return $rows->map(function (\stdClass $row) use ($allTimeEntries, $allRoleSlugs) {
            $timeEntries = $allTimeEntries[$row->id] ?? [];
            $roleSlugs = $allRoleSlugs[$row->id] ?? [];

            return $this->toDomainEntity($row, $timeEntries, $roleSlugs);
        })->toArray();
    }

    public function delete(UserId $id): void
    {
        $deleted = $this->connection->transaction(function () use ($id) {
            $this->timeEntryQuery()->where('user_id', $id->value())->delete();

            return $this->query()->where('id', $id->value())->delete() > 0;
        });

        if (!$deleted) {
            throw UserDeletionFailedException::withId($id);
        }
    }

    public function count(): int
    {
        return $this->query()->count();
    }

    public function countTodayRegistrations(): int
    {
        $today = $this->todayBounds();

        return $this->query()
            ->where('created_at', '>=', $today['start'])
            ->where('created_at', '<=', $today['end'])
            ->count();
    }

    /** @return array<array-key, mixed> */
    public function findTodayTimeEntriesByUserId(UserId $id): array
    {
        $today = $this->todayBounds();

        return $this->timeEntryQuery()
            ->where('user_id', $id->value())
            ->where('entrada', '>=', $today['start'])
            ->where('entrada', '<=', $today['end'])
            ->get()
            ->map(fn (\stdClass $row) => (array) $row)
            ->toArray();
    }

    /** @return User[] */
    public function findAdmins(): array
    {
        $adminUserIds = $this->connection->table('user_role')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->whereIn('roles.slug', ['super_admin', 'admin'])
            ->pluck('user_role.user_id')
            ->unique()
            ->toArray();

        if (empty($adminUserIds)) {
            return [];
        }

        $rows = $this->query()->whereIn('id', $adminUserIds)->get();
        $allTimeEntries = $this->getTimeEntriesForUsers($adminUserIds);
        $allRoleSlugs = $this->getRoleSlugsForUsers($adminUserIds);

        return $rows->map(function (\stdClass $row) use ($allTimeEntries, $allRoleSlugs) {
            $timeEntries = $allTimeEntries[$row->id] ?? [];
            $roleSlugs = $allRoleSlugs[$row->id] ?? [];

            return $this->toDomainEntity($row, $timeEntries, $roleSlugs);
        })->toArray();
    }

    /**
     * @return array{cerrados: \Illuminate\Support\Collection<int, \stdClass>, abiertos: \Illuminate\Support\Collection<int, \stdClass>}
     */
    public function findDailyTimeEntriesByUserId(UserId $id): array
    {
        $userId = $id->value();

        return [
            'cerrados' => $this->timeEntryQuery()
                ->where('user_id', $userId)
                ->whereNotNull('salida')
                ->orderBy('entrada', 'desc')
                ->get(),
            // Include ALL open entries, not just today's (user might have forgotten to close from previous days)
            'abiertos' => $this->timeEntryQuery()
                ->where('user_id', $userId)
                ->whereNull('salida')
                ->orderBy('entrada', 'desc')
                ->get(),
        ];
    }

    /** @return array{start: int, end: int} */
    private function todayBounds(): array
    {
        return [
            'start' => strtotime('today 00:00:00'),
            'end' => strtotime('today 23:59:59'),
        ];
    }

    protected function query(): Builder
    {
        return $this->connection->table($this->userTable);
    }

    protected function timeEntryQuery(): Builder
    {
        return $this->connection->table($this->timeEntryTable);
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function getTimeEntriesForUser(int $userId): array
    {
        return $this->timeEntryQuery()
            ->where('user_id', $userId)
            ->get()
            ->map(fn (\stdClass $entry) => [
                'id' => $entry->id,
                'user_id' => $entry->user_id,
                'entrada' => $entry->entrada,
                'salida' => $entry->salida,
                'auto_closed' => $entry->auto_closed,
                'auto_close_reason' => $entry->auto_close_reason,
            ])->toArray();
    }

    /**
     * @param array<int> $userIds
     *
     * @return array<int, array<array-key, mixed>>
     */
    private function getTimeEntriesForUsers(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        return $this->timeEntryQuery()
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id')
            ->map(fn ($entries) => $entries->map(fn (\stdClass $entry) => [
                'id' => $entry->id,
                'user_id' => $entry->user_id,
                'entrada' => $entry->entrada,
                'salida' => $entry->salida,
                'auto_closed' => $entry->auto_closed,
                'auto_close_reason' => $entry->auto_close_reason,
            ])->toArray())
            ->toArray();
    }

    /**
     * @return string[]
     */
    protected function getRoleSlugsForUser(int $userId): array
    {
        return $this->connection->table($this->userRoleTable)
            ->join($this->roleTable, "{$this->userRoleTable}.role_id", '=', "{$this->roleTable}.id")
            ->where("{$this->userRoleTable}.user_id", $userId)
            ->pluck("{$this->roleTable}.slug")
            ->toArray();
    }

    /**
     * @param int[] $userIds
     *
     * @return array<int, string[]>
     */
    private function getRoleSlugsForUsers(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        return $this->connection->table($this->userRoleTable)
            ->join($this->roleTable, "{$this->userRoleTable}.role_id", '=', "{$this->roleTable}.id")
            ->whereIn("{$this->userRoleTable}.user_id", $userIds)
            ->select("{$this->userRoleTable}.user_id", "{$this->roleTable}.slug")
            ->get()
            ->groupBy('user_id')
            ->map(fn ($roles) => $roles->pluck('slug')->toArray())
            ->toArray();
    }

    /**
     * @param array<array-key, mixed> $timeEntries
     * @param string[]                $roleSlugs
     */
    protected function toDomainEntity(\stdClass $row, array $timeEntries, array $roleSlugs = []): User
    {
        return User::fromPrimitives(
            $row->id,
            $row->uuid,
            $row->email,
            $row->name,
            (bool) ($row->is_active ?? true),
            $timeEntries,
            $roleSlugs
        );
    }
}
