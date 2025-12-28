<?php

namespace App\DDD\User\Infrastructure;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class,EloquentUserRepository::class);
    }

    public function boot(): void
    {
        //
    }
}


