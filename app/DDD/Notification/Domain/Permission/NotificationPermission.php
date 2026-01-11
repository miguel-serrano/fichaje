<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain\Permission;

enum NotificationPermission: string
{
    case ViewOwn = 'notification.view_own';
    case ViewAll = 'notification.view_all';
    case Send = 'notification.send';
    case MarkRead = 'notification.mark_read';
    case Delete = 'notification.delete';
}
