<?php

declare(strict_types=1);

namespace App\DDD\Administration\Infrastructure;

use App\DDD\Administration\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\Voter\AdministrationVoter;
use App\DDD\Administration\Infrastructure\Persistence\Eloquent\EloquentPermissionRepository;
use App\DDD\Administration\Infrastructure\Persistence\Eloquent\EloquentRoleRepository;
use App\DDD\Authorization\Infrastructure\LaravelServiceProvider as AuthorizationServiceProvider;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            RoleRepositoryInterface::class,
            EloquentRoleRepository::class,
        );

        $this->app->bind(
            PermissionRepositoryInterface::class,
            EloquentPermissionRepository::class,
        );

        $this->app->bind(AdministrationVoter::class);
        AuthorizationServiceProvider::tagVoter($this, AdministrationVoter::class);
    }

    public function boot(): void
    {
    }
}
