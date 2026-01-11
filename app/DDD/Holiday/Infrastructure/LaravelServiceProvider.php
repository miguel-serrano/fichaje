<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Infrastructure;

use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Infrastructure\Persistence\Eloquent\EloquentHolidayRepository;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            HolidayRepositoryInterface::class,
            EloquentHolidayRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
