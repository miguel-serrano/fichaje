<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Permission;

enum TimeTrackingPermission: string
{
    case ViewOwn = 'timetracking.view_own';
    case ViewAll = 'timetracking.view_all';
    case ClockIn = 'timetracking.clockin';
    case ClockOut = 'timetracking.clockout';
    case EditOwn = 'timetracking.edit_own';
    case EditAny = 'timetracking.edit_any';
    case DeleteAny = 'timetracking.delete_any';
    case Reports = 'timetracking.reports';
}
