<?php

namespace App\Providers;

use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\Notification\Infrastructure\Subscriber\SendDomainEventEmailSubscriber;
use App\DDD\Shared\Domain\Event\DomainEventRepositoryInterface;
use App\DDD\Shared\Domain\Event\EventBusInterface;
use App\DDD\Shared\Infrastructure\Event\LaravelEventBus;
use App\DDD\Shared\Infrastructure\Persistence\Eloquent\EloquentDomainEventRepository;
use App\DDD\User\Domain\Permission\UserPermission;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\View;
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
    public function boot(): void
    {
        View::composer('layouts.app', function (ViewContract $view): void {
            $view->with('navigation', $this->navigationPermissions());
        });
    }

    /**
     * Qué secciones del menú puede ver el usuario autenticado, decidido por los voters.
     *
     * @return array{users: bool, roles: bool, permissions: bool, holidayReview: bool}
     */
    private function navigationPermissions(): array
    {
        $userId = auth()->id();

        if (null === $userId) {
            return ['users' => false, 'roles' => false, 'permissions' => false, 'holidayReview' => false];
        }

        $authorization = $this->app->make(AuthorizationServiceInterface::class);
        $can = fn (\BackedEnum $permission): bool => $authorization->isGranted($permission->value, (int) $userId);

        return [
            'users' => $can(UserPermission::View),
            'roles' => $can(AdministrationPermission::ManageRoles),
            'permissions' => $can(AdministrationPermission::ManagePermissions),
            'holidayReview' => $can(HolidayPermission::ViewPending) && $can(HolidayPermission::ViewApproved),
        ];
    }
}
