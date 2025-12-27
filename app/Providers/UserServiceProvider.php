<?php

namespace App\Providers;

use App\DDD\User\Application\Handler\CreateUserCommandHandler;
use App\DDD\User\Application\Handler\DeleteUserCommandHandler;
use App\DDD\User\Application\Handler\GetAllUsersWithTimeQueryHandler;
use App\DDD\User\Application\Handler\GetUserByIdQueryHandler;
use App\DDD\User\Application\Handler\GetUserDailyRegistrosQueryHandler;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository binding moved to LaravelServiceProvider
        // $this->app->singleton(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->singleton(CreateUserCommandHandler::class);
        $this->app->singleton(DeleteUserCommandHandler::class);
        $this->app->singleton(GetUserByIdQueryHandler::class);
        $this->app->singleton(GetAllUsersWithTimeQueryHandler::class);
        $this->app->singleton(GetUserDailyRegistrosQueryHandler::class);
    }
}

