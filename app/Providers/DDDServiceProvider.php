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

        // Register User services
        $this->app->bind(
            \App\DDD\User\Domain\Services\UserAuthorizationServiceInterface::class,
            \App\DDD\User\Infrastructure\Services\UserAuthorizationService::class
        );

        // Register Authentication services
        $this->app->bind(
            \App\DDD\Authentication\Domain\Services\AuthenticationService::class,
            \App\DDD\Authentication\Infrastructure\LaravelAuthenticationService::class
        );
        $this->app->bind(
            \App\DDD\Authentication\Domain\Services\PasswordHashingService::class,
            \App\DDD\Authentication\Infrastructure\LaravelPasswordHashingService::class
        );
    }

    public function boot(): void
    {
        $tacticianBus = $this->app->make(TacticianCommandBusInterface::class);

        $this->mapCommands($tacticianBus);
        $this->mapQueries($tacticianBus);
    }

    private function mapCommands(TacticianCommandBusInterface $tacticianBus): void
    {
        // User Commands
        $tacticianBus->addHandler(
            \App\DDD\User\Application\Command\CreateUserCommand::class,
            \App\DDD\User\Application\Handler\CreateUserCommandHandler::class
        );

        $tacticianBus->addHandler(
            \App\DDD\User\Application\Command\DeleteUserCommand::class,
            \App\DDD\User\Application\Handler\DeleteUserCommandHandler::class
        );

        $tacticianBus->addHandler(
            \App\DDD\User\Application\Command\ToggleUserActiveCommand::class,
            \App\DDD\User\Application\Handler\ToggleUserActiveCommandHandler::class
        );

        // TimeTracking Commands
        $tacticianBus->addHandler(
            \App\DDD\TimeTracking\Application\Command\ClockInCommand::class,
            \App\DDD\TimeTracking\Application\Handler\ClockInCommandHandler::class
        );

        $tacticianBus->addHandler(
            \App\DDD\TimeTracking\Application\Command\ClockOutCommand::class,
            \App\DDD\TimeTracking\Application\Handler\ClockOutCommandHandler::class
        );

        // Authentication Commands
        $tacticianBus->addHandler(
            \App\DDD\Authentication\Application\Command\LoginCommand::class,
            \App\DDD\Authentication\Application\Handler\LoginCommandHandler::class
        );

        $tacticianBus->addHandler(
            \App\DDD\Authentication\Application\Command\RegisterCommand::class,
            \App\DDD\Authentication\Application\Handler\RegisterCommandHandler::class
        );

        $tacticianBus->addHandler(
            \App\DDD\Authentication\Application\Command\LogoutCommand::class,
            \App\DDD\Authentication\Application\Handler\LogoutCommandHandler::class
        );
    }

    private function mapQueries(TacticianCommandBusInterface $tacticianBus): void
    {
        // User Queries
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

        // TimeTracking Queries
        $tacticianBus->addHandler(
            \App\DDD\TimeTracking\Application\Query\GetAccumulatedSecondsQuery::class,
            \App\DDD\TimeTracking\Application\Handler\GetAccumulatedSecondsQueryHandler::class
        );

        $tacticianBus->addHandler(
            \App\DDD\TimeTracking\Application\Query\HasOpenTimeEntryQuery::class,
            \App\DDD\TimeTracking\Application\Handler\HasOpenTimeEntryQueryHandler::class
        );

        // Authentication Queries
        $tacticianBus->addHandler(
            \App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery::class,
            \App\DDD\Authentication\Application\Handler\GetAuthenticatedUserQueryHandler::class
        );
    }
}
