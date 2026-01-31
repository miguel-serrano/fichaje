<?php

declare(strict_types=1);

namespace App\DDD\Administration\Domain\Permission;

enum AdministrationPermission: string
{
    case ManageRoles = 'authorization.manage_roles';
    case ManagePermissions = 'authorization.manage_permissions';
    case AssignRoles = 'authorization.assign_roles';
}
