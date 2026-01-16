<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain\ValueObjects;

enum NotificationType: string
{
    case TimeEntryAutoClosed = 'time_entry_auto_closed';
    case HolidayRequested = 'holiday_requested';
    case HolidayApproved = 'holiday_approved';
    case HolidayRejected = 'holiday_rejected';
}
