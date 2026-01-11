<?php

declare(strict_types=1);

namespace App\DDD\Notification\Infrastructure;

use App\DDD\Notification\Domain\Channel;
use App\DDD\Notification\Domain\Interface\NotifierInterface;
use App\DDD\Notification\Domain\Notification;
use App\DDD\User\Domain\Entity\User;
use App\Models\Notification as NotificationModel;
use Illuminate\Database\ConnectionInterface;

final class DatabaseNotifier implements NotifierInterface
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {
    }

    public function send(User $user, Notification $notification): void
    {
        $this->connection->table(NotificationModel::tableName())->insert([
            'user_id' => $user->id()->value(),
            'type' => $notification->type()->value,
            'title' => $notification->title(),
            'message' => $notification->message(),
            'data' => json_encode($notification->data()),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function supports(Channel $channel): bool
    {
        return Channel::Database === $channel;
    }
}
