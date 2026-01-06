<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain;

enum NotificationType: string
{
    case TimeEntryAutoClosed = 'time_entry_auto_closed';
}
