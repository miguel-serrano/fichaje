<?php

namespace App\DDD\User\Infrastructure;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserCreationValidator;
use App\DDD\User\Domain\Services\UserExistValidator;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        $this->app->bind(UserCreationValidator::class, function ($app) {
            return new UserCreationValidator(
                $app->make(UserRepositoryInterface::class),
                config('users.limits.max_users', 96)
            );
        });

        $this->app->bind(UserExistValidator::class, function ($app) {
            return new UserExistValidator(
                $app->make(UserRepositoryInterface::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
