<?php

namespace App\Providers;

use App\DDD\User\Application\Handler\DeleteUserCommandHandler;
use App\DDD\User\Application\Handler\GetAllUsersWithTimeQueryHandler;
use App\DDD\User\Application\Handler\GetUserByIdQueryHandler;
use App\DDD\User\Application\Handler\GetUserDailyRegistrosQueryHandler;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeleteUserCommandHandler::class);
        $this->app->singleton(GetUserByIdQueryHandler::class);
        $this->app->singleton(GetAllUsersWithTimeQueryHandler::class);
        $this->app->singleton(GetUserDailyRegistrosQueryHandler::class);
    }
}
