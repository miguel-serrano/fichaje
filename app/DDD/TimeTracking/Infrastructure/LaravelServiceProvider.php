<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Infrastructure;

use App\DDD\Authorization\Domain\Interface\UserPermissionsCheckerInterface;
use App\DDD\Authorization\Infrastructure\LaravelServiceProvider as AuthorizationServiceProvider;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Interface\ClockInValidatorInterface;
use App\DDD\TimeTracking\Domain\Interface\ClockOutValidatorInterface;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Voter\TimeTrackingVoter;
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
                $app->make(UserPermissionsCheckerInterface::class),
                $app->make(LoggerInterface::class)
            );
        });

        $this->app->bind(ClockInValidatorInterface::class, function ($app) {
            return $app->make(TimeTrackingService::class);
        });

        $this->app->bind(ClockOutValidatorInterface::class, function ($app) {
            return $app->make(TimeTrackingService::class);
        });

        $this->app->bind(TimeTrackingVoter::class);
        AuthorizationServiceProvider::tagVoter($this, TimeTrackingVoter::class);
    }

    public function boot(): void
    {
    }
}
