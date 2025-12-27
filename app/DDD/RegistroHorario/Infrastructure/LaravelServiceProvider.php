<?php

namespace App\DDD\RegistroHorario\Infrastructure;

use App\DDD\RegistroHorario\Domain\RegistroHorarioRepositoryInterface;
use App\DDD\RegistroHorario\Infrastructure\Persistence\Eloquent\RegistroHorarioRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            RegistroHorarioRepositoryInterface::class,
            RegistroHorarioRepositoryEloquent::class
        );
    }

    public function boot(): void
    {
        //
    }
}
