<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Infrastructure;

use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\Policy\HolidayPolicy;
use App\DDD\Holiday\Domain\Policy\HolidayPolicyInterface;
use App\DDD\Holiday\Domain\Services\HolidayAuthorizationServiceInterface;
use App\DDD\Holiday\Infrastructure\Persistence\Eloquent\EloquentHolidayRepository;
use App\DDD\Holiday\Infrastructure\Services\HolidayAuthorizationService;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            HolidayRepositoryInterface::class,
            EloquentHolidayRepository::class
        );

        $this->app->bind(
            HolidayPolicyInterface::class,
            HolidayPolicy::class
        );

        $this->app->bind(
            HolidayAuthorizationServiceInterface::class,
            HolidayAuthorizationService::class
        );
    }

    public function boot(): void
    {
    }
}
