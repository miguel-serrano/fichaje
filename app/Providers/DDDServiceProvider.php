<?php

namespace App\Providers;

use App\DDD\Authentication\Application\Command\LoginCommand;
use App\DDD\Authentication\Application\Command\LogoutCommand;
use App\DDD\Authentication\Application\Command\RegisterCommand;
use App\DDD\Authentication\Application\Handler\GetAuthenticatedUserQueryHandler;
use App\DDD\Authentication\Application\Handler\LoginCommandHandler;
use App\DDD\Authentication\Application\Handler\LogoutCommandHandler;
use App\DDD\Authentication\Application\Handler\RegisterCommandHandler;
use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authentication\Domain\Services\AuthenticationService;
use App\DDD\Authentication\Domain\Services\PasswordHashingService;
use App\DDD\Authentication\Infrastructure\LaravelAuthenticationService;
use App\DDD\Authentication\Infrastructure\LaravelPasswordHashingService;
use App\DDD\Notification\Application\NotificationService;
use App\DDD\Notification\Infrastructure\DatabaseNotifier;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\Shared\Infrastructure\Bus\LaravelTacticianCommandBus;
use App\DDD\Shared\Infrastructure\Bus\LaravelTacticianQueryBus;
use App\DDD\TimeTracking\Application\Command\ClockInCommand;
use App\DDD\TimeTracking\Application\Command\ClockOutCommand;
use App\DDD\TimeTracking\Application\Command\CloseOrphanTimeEntriesCommand;
use App\DDD\TimeTracking\Application\Handler\ClockInCommandHandler;
use App\DDD\TimeTracking\Application\Handler\ClockOutCommandHandler;
use App\DDD\TimeTracking\Application\Handler\CloseOrphanTimeEntriesCommandHandler;
use App\DDD\TimeTracking\Application\Handler\GetAccumulatedSecondsQueryHandler;
use App\DDD\TimeTracking\Application\Handler\HasOpenTimeEntryQueryHandler;
use App\DDD\TimeTracking\Application\Query\GetAccumulatedSecondsQuery;
use App\DDD\TimeTracking\Application\Query\HasOpenTimeEntryQuery;
use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Application\Command\ToggleUserActiveCommand;
use App\DDD\User\Application\Handler\DeleteUserCommandHandler;
use App\DDD\User\Application\Handler\GetAllUsersWithTimeQueryHandler;
use App\DDD\User\Application\Handler\GetUserByIdQueryHandler;
use App\DDD\User\Application\Handler\GetUserDailyRegistrosQueryHandler;
use App\DDD\User\Application\Handler\GetUserTodayRegistrosQueryHandler;
use App\DDD\User\Application\Handler\ToggleUserActiveCommandHandler;
use App\DDD\User\Application\Query\GetAllUsersWithTimeQuery;
use App\DDD\User\Application\Query\GetUserByIdQuery;
use App\DDD\User\Application\Query\GetUserDailyRegistrosQuery;
use App\DDD\User\Application\Query\GetUserTodayRegistrosQuery;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;
use App\DDD\User\Infrastructure\Services\UserAuthorizationService;
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
        $this->app->bind(UserAuthorizationServiceInterface::class,UserAuthorizationService::class);

        // Register Authentication services
        $this->app->bind(AuthenticationService::class, LaravelAuthenticationService::class);
        $this->app->bind(PasswordHashingService::class, LaravelPasswordHashingService::class);

        // Register Notification services
        $this->app->singleton(
            NotificationService::class,
            function ($app) {
                return new NotificationService([
                    $app->make(DatabaseNotifier::class),
                ]);
            }
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
        $tacticianBus->addHandler(DeleteUserCommand::class, DeleteUserCommandHandler::class);
        $tacticianBus->addHandler(ToggleUserActiveCommand::class, ToggleUserActiveCommandHandler::class);

        // TimeTracking Commands
        $tacticianBus->addHandler(ClockInCommand::class, ClockInCommandHandler::class);
        $tacticianBus->addHandler(ClockOutCommand::class, ClockOutCommandHandler::class);
        $tacticianBus->addHandler(CloseOrphanTimeEntriesCommand::class, CloseOrphanTimeEntriesCommandHandler::class);

        // Authentication Commands
        $tacticianBus->addHandler(LoginCommand::class, LoginCommandHandler::class);
        $tacticianBus->addHandler(RegisterCommand::class, RegisterCommandHandler::class);
        $tacticianBus->addHandler(LogoutCommand::class, LogoutCommandHandler::class);
    }

    private function mapQueries(TacticianCommandBusInterface $tacticianBus): void
    {
        // User Queries
        $tacticianBus->addHandler(GetUserByIdQuery::class, GetUserByIdQueryHandler::class);
        $tacticianBus->addHandler(GetAllUsersWithTimeQuery::class, GetAllUsersWithTimeQueryHandler::class);
        $tacticianBus->addHandler(GetUserDailyRegistrosQuery::class, GetUserDailyRegistrosQueryHandler::class);
        $tacticianBus->addHandler(GetUserTodayRegistrosQuery::class, GetUserTodayRegistrosQueryHandler::class);

        // TimeTracking Queries
        $tacticianBus->addHandler(GetAccumulatedSecondsQuery::class, GetAccumulatedSecondsQueryHandler::class);
        $tacticianBus->addHandler(HasOpenTimeEntryQuery::class, HasOpenTimeEntryQueryHandler::class);

        // Authentication Queries
        $tacticianBus->addHandler(GetAuthenticatedUserQuery::class, GetAuthenticatedUserQueryHandler::class);
    }
}
