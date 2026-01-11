<?php

declare(strict_types=1);

namespace App\DDD\Authentication\Domain\Permission;

enum AuthenticationPermission: string
{
    case Login = 'authentication.login';
    case Logout = 'authentication.logout';
    case Register = 'authentication.register';
    case ResetPassword = 'authentication.reset_password';
    case Impersonate = 'authentication.impersonate';
}
