<?php

namespace App\DDD\Notification\Infrastructure\Persistence\Eloquent;

use App\DDD\Notification\Domain\Interface\NotificationRepositoryInterface;
use App\DDD\Notification\Domain\ValueObjects\NotificationId;
use App\DDD\Shared\Domain\ValueObject\UnixTimestamp;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\Notification as NotificationModel;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    private string $tableName;

    public function __construct(private ConnectionInterface $connection)
    {
        $this->tableName = NotificationModel::tableName();
    }

    public function markAsRead(NotificationId $id, UserId $userId): bool
    {
        $updated = $this->query()
            ->where('id', $id->value())
            ->where('user_id', $userId->value())
            ->whereNull('read_at')
            ->update(['read_at' => UnixTimestamp::now()->value()]);

        return $updated > 0;
    }

    public function markAllAsRead(UserId $userId): int
    {
        return $this->query()
            ->where('user_id', $userId->value())
            ->whereNull('read_at')
            ->update(['read_at' => UnixTimestamp::now()->value()]);
    }

    private function query(): Builder
    {
        return $this->connection->table($this->tableName);
    }
}
