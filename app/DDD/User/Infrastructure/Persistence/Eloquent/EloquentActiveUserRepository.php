<?php

declare(strict_types=1);

namespace App\DDD\User\Infrastructure\Persistence\Eloquent;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UserNotActiveException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\ActiveUserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Uuid;
use App\Models\Role as RoleModel;
use App\Models\TimeEntry as TimeEntryModel;
use App\Models\User as UserModel;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

class EloquentActiveUserRepository implements ActiveUserRepositoryInterface
{
    private string $userTable;

    private string $timeEntryTable;

    private string $userRoleTable;

    private string $roleTable;

    public function __construct(private ConnectionInterface $connection)
    {
        $this->userTable = UserModel::tableName();
        $this->timeEntryTable = TimeEntryModel::tableName();
        $this->userRoleTable = UserRole::tableName();
        $this->roleTable = RoleModel::tableName();
    }

    public function findActiveByUuidOrFail(Uuid $uuid): User
    {
        $row = $this->query()->where('uuid', $uuid->value())->first();

        if (!$row) {
            throw UserNotFoundException::byUuid($uuid);
        }

        if (!$row->is_active) {
            throw UserNotActiveException::forUuid($uuid);
        }

        $timeEntries = $this->getTimeEntriesForUser($row->id);

        $roleSlugs = $this->getRoleSlugsForUser($row->id);

        return $this->toDomainEntity($row, $timeEntries, $roleSlugs);
    }

    private function query(): Builder
    {
        return $this->connection->table($this->userTable);
    }

    private function timeEntryQuery(): Builder
    {
        return $this->connection->table($this->timeEntryTable);
    }

    /**
     * @return array<array-key, mixed>
     */
    private function getTimeEntriesForUser(int $userId): array
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
     * @return string[]
     */
    private function getRoleSlugsForUser(int $userId): array
    {
        return $this->connection->table($this->userRoleTable)
            ->join($this->roleTable, "{$this->userRoleTable}.role_id", '=', "{$this->roleTable}.id")
            ->where("{$this->userRoleTable}.user_id", $userId)
            ->pluck("{$this->roleTable}.slug")
            ->toArray();
    }

    /**
     * @param array<array-key, mixed> $timeEntries
     * @param string[]                $roleSlugs
     */
    private function toDomainEntity(\stdClass $row, array $timeEntries, array $roleSlugs = []): User
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
