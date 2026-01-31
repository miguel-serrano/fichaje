<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Infrastructure;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Authorization\Domain\Interface\UserPermissionsCheckerInterface;
use App\DDD\Authorization\Infrastructure\Service\AuthorizationService;
use App\DDD\Authorization\Infrastructure\Service\UserPermissionsChecker;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserPermissionsCheckerInterface::class,
            UserPermissionsChecker::class,
        );

        $this->app->singleton(AuthorizationServiceInterface::class, function ($app) {
            $service = new AuthorizationService();

            foreach ($app->tagged('voters') as $voter) {
                $service->registerVoter($voter);
            }

            return $service;
        });
    }

    /**
     * Helper para que otros ServiceProviders registren sus voters.
     */
    public static function tagVoter(ServiceProvider $provider, string $voterClass): void
    {
        $provider->app->tag([$voterClass], 'voters');
    }

    public function boot(): void
    {
    }
}
