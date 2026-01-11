<?php

namespace App\DDD\Authorization\Domain\ValueObjects;

enum BoundedContext: string
{
    case User = 'User';
    case TimeTracking = 'TimeTracking';
    case Holiday = 'Holiday';
    case Authorization = 'Authorization';
    case Authentication = 'Authentication';
    case Notification = 'Notification';
    case System = 'System';
}
