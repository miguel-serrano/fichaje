<?php

namespace App\Providers;

use App\DDD\Shared\Domain\Event\DomainEventRepositoryInterface;
use App\DDD\Shared\Domain\Event\EventBusInterface;
use App\DDD\Shared\Infrastructure\Event\LaravelEventBus;
use App\DDD\Shared\Infrastructure\Persistence\Eloquent\EloquentDomainEventRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DomainEventRepositoryInterface::class, EloquentDomainEventRepository::class);

        $this->app->singleton(EventBusInterface::class, function ($app) {
            return new LaravelEventBus(
                $app->make(DomainEventRepositoryInterface::class),
                $app,
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
