<?php

namespace App\DDD\TimeTracking\Infrastructure;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Infrastructure\Persistence\Eloquent\EloquentTimeEntryRepository;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class LaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TimeEntryRepositoryInterface::class,
            EloquentTimeEntryRepository::class
        );

        $this->app->bind(TimeTrackingService::class, function ($app) {
            return TimeTrackingService::create(
                $app->make(UserRepositoryInterface::class),
                $app->make(TimeEntryRepositoryInterface::class),
                $app->make(PermissionCheckerInterface::class),
                $app->make(LoggerInterface::class)
            );
        });
    }

    public function boot(): void
    {
    }
}
