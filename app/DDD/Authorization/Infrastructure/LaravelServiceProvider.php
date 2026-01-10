<?php

namespace App\DDD\Authorization\Infrastructure;

use App\DDD\Authorization\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Authorization\Infrastructure\Persistence\Eloquent\EloquentPermissionRepository;
use App\DDD\Authorization\Infrastructure\Persistence\Eloquent\EloquentRoleRepository;
use App\DDD\Authorization\Infrastructure\Services\PermissionChecker;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            RoleRepositoryInterface::class,
            EloquentRoleRepository::class
        );

        $this->app->bind(
            PermissionRepositoryInterface::class,
            EloquentPermissionRepository::class
        );

        $this->app->bind(
            PermissionCheckerInterface::class,
            PermissionChecker::class
        );
    }

    public function boot(): void
    {
        // Register command handlers with Tactician
        // This will be done in DDDServiceProvider
    }
}
