<?php

namespace App\Providers;

use App\DDD\Notification\Infrastructure\Subscriber\SendDomainEventEmailSubscriber;
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
            $eventBus = new LaravelEventBus(
                $app->make(DomainEventRepositoryInterface::class),
                $app,
            );
            $eventBus->subscribe(SendDomainEventEmailSubscriber::class);

            return $eventBus;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
