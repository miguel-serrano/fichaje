<?php

namespace App\Providers;

use App\DDD\User\Application\CreateUserUseCase;
use App\DDD\User\Application\GetUserByIdUseCase;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use App\DDD\User\Domain\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->singleton(CreateUserUseCase::class);
        $this->app->singleton(GetUserByIdUseCase::class);
    }
}