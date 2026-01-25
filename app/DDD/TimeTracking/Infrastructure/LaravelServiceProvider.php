<?php

namespace App\DDD\TimeTracking\Infrastructure;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Policy\TimeTrackingPolicy;
use App\DDD\TimeTracking\Domain\Policy\TimeTrackingPolicyInterface;
use App\DDD\TimeTracking\Domain\Services\TimeTrackingAuthorizationServiceInterface;
use App\DDD\TimeTracking\Infrastructure\Persistence\Eloquent\EloquentTimeEntryRepository;
use App\DDD\TimeTracking\Infrastructure\Services\TimeTrackingAuthorizationService;
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

        $this->app->bind(TimeTrackingPolicyInterface::class, TimeTrackingPolicy::class);

        $this->app->bind(
            TimeTrackingAuthorizationServiceInterface::class,
            TimeTrackingAuthorizationService::class
        );
    }

    public function boot(): void
    {
    }
}
