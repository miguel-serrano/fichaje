<?php

declare(strict_types=1);

namespace App\DDD\User\Domain\Permission;

enum UserPermission: string
{
    case View = 'user.view';
    case ViewOwn = 'user.view_own';
    case Create = 'user.create';
    case Update = 'user.update';
    case UpdateOwn = 'user.update_own';
    case Delete = 'user.delete';
    case ToggleActive = 'user.toggle_active';
}
