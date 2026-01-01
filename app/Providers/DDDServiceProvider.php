<?php

namespace App\Providers;

use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\Shared\Infrastructure\Bus\LaravelTacticianCommandBus;
use App\DDD\Shared\Infrastructure\Bus\LaravelTacticianQueryBus;
use Illuminate\Support\ServiceProvider;
use Joselfonseca\LaravelTactician\CommandBusInterface as TacticianCommandBusInterface;

class DDDServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register our domain buses
        $this->app->bind(CommandBusInterface::class, LaravelTacticianCommandBus::class);
        $this->app->bind(QueryBusInterface::class, LaravelTacticianQueryBus::class);
    }

    public function boot(): void
    {
        // Map commands and queries to handlers
        $this->mapCommandsAndQueries();
    }

    private function mapCommandsAndQueries(): void
    {
        $tacticianBus = $this->app->make(TacticianCommandBusInterface::class);

        // User Commands and Queries
        $tacticianBus->addHandler(
            \App\DDD\User\Application\Command\CreateUserCommand::class,
            \App\DDD\User\Application\Handler\CreateUserCommandHandler::class
        );

        $tacticianBus->addHandler(
            \App\DDD\User\Application\Command\DeleteUserCommand::class,
            \App\DDD\User\Application\Handler\DeleteUserCommandHandler::class
        );

        $tacticianBus->addHandler(
            \App\DDD\User\Application\Command\GetUserByIdQuery::class,
            \App\DDD\User\Application\Handler\GetUserByIdQueryHandler::class
        );

        $tacticianBus->addHandler(
            \App\DDD\User\Application\Command\GetAllUsersWithTimeQuery::class,
            \App\DDD\User\Application\Handler\GetAllUsersWithTimeQueryHandler::class
        );

        $tacticianBus->addHandler(
            \App\DDD\User\Application\Command\GetUserDailyRegistrosQuery::class,
            \App\DDD\User\Application\Handler\GetUserDailyRegistrosQueryHandler::class
        );

        // TimeTracking Commands and Queries
        $tacticianBus->addHandler(
            \App\DDD\TimeTracking\Application\Command\ClockInCommand::class,
            \App\DDD\TimeTracking\Application\Handler\ClockInCommandHandler::class
        );

        $tacticianBus->addHandler(
            \App\DDD\TimeTracking\Application\Command\ClockOutCommand::class,
            \App\DDD\TimeTracking\Application\Handler\ClockOutCommandHandler::class
        );

        $tacticianBus->addHandler(
            \App\DDD\TimeTracking\Application\Query\GetAccumulatedSecondsQuery::class,
            \App\DDD\TimeTracking\Application\Handler\GetAccumulatedSecondsQueryHandler::class
        );

        $tacticianBus->addHandler(
            \App\DDD\TimeTracking\Application\Query\HasOpenTimeEntryQuery::class,
            \App\DDD\TimeTracking\Application\Handler\HasOpenTimeEntryQueryHandler::class
        );
    }
}
