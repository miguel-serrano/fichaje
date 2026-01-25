<?php

namespace App\DDD\Notification\Domain\Interface;

use App\DDD\Notification\Domain\ValueObjects\NotificationId;
use App\DDD\User\Domain\ValueObjects\UserId;

interface NotificationRepositoryInterface
{
    public function markAsRead(NotificationId $id, UserId $userId): bool;

    public function markAllAsRead(UserId $userId): int;
}
