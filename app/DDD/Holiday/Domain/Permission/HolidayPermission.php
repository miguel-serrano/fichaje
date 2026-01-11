<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Permission;

enum HolidayPermission: string
{
    case Request = 'holiday.request';
    case ViewOwn = 'holiday.view_own';
    case ViewPending = 'holiday.view_pending';
    case ViewApproved = 'holiday.view_approved';
    case Approve = 'holiday.approve';
    case Reject = 'holiday.reject';
}
