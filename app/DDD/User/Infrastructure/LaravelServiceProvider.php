<?php

declare(strict_types=1);

namespace App\DDD\User\Infrastructure;

use App\DDD\Authorization\Infrastructure\LaravelServiceProvider as AuthorizationServiceProvider;
use App\DDD\User\Domain\Interface\ActiveUserRepositoryInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserCreationPolicyService;
use App\DDD\User\Domain\Voter\UserVoter;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentActiveUserRepository;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(ActiveUserRepositoryInterface::class, EloquentActiveUserRepository::class);

        $this->app->bind(UserCreationPolicyService::class, function ($app) {
            return new UserCreationPolicyService(
                $app->make(UserRepositoryInterface::class),
                config('users.limits.max_users'),
                config('users.limits.daylimit')
            );
        });

        $this->app->bind(UserVoter::class);
        AuthorizationServiceProvider::tagVoter($this, UserVoter::class);
    }

    public function boot(): void
    {
    }
}
