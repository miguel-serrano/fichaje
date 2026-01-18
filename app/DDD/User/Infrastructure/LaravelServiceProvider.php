<?php

namespace App\DDD\User\Infrastructure;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserCreationPolicyService;
use App\DDD\User\Domain\Specification\UniqueEmailSpecification;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        $this->app->bind(UniqueEmailSpecification::class, function ($app) {
            return new UniqueEmailSpecification(
                $app->make(UserRepositoryInterface::class)
            );
        });

        $this->app->bind(UserCreationPolicyService::class, function ($app) {
            return new UserCreationPolicyService(
                $app->make(UserRepositoryInterface::class),
                config('users.limits.max_users'),
                config('users.limits.daylimit')
            );
        });
    }

    public function boot(): void
    {
    }
}
