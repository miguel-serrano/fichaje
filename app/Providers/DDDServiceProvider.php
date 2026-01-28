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
use App\DDD\Authorization\Application\Command\AssignRoleToUserCommand;
use App\DDD\Authorization\Application\Command\CreatePermissionCommand;
use App\DDD\Authorization\Application\Command\CreateRoleCommand;
use App\DDD\Authorization\Application\Command\DeletePermissionCommand;
use App\DDD\Authorization\Application\Command\DeleteRoleCommand;
use App\DDD\Authorization\Application\Command\RemoveRoleFromUserCommand;
use App\DDD\Authorization\Application\Command\SyncPermissionsToRoleCommand;
use App\DDD\Authorization\Application\Command\UpdatePermissionCommand;
use App\DDD\Authorization\Application\Command\UpdateRoleCommand;
use App\DDD\Authorization\Application\Handler\AssignRoleToUserCommandHandler;
use App\DDD\Authorization\Application\Handler\CreatePermissionCommandHandler;
use App\DDD\Authorization\Application\Handler\CreateRoleCommandHandler;
use App\DDD\Authorization\Application\Handler\DeletePermissionCommandHandler;
use App\DDD\Authorization\Application\Handler\DeleteRoleCommandHandler;
use App\DDD\Authorization\Application\Handler\GetAllPermissionsQueryHandler;
use App\DDD\Authorization\Application\Handler\GetAllRolesQueryHandler;
use App\DDD\Authorization\Application\Handler\GetPermissionByIdQueryHandler;
use App\DDD\Authorization\Application\Handler\GetRoleByIdQueryHandler;
use App\DDD\Authorization\Application\Handler\GetUserPermissionsQueryHandler;
use App\DDD\Authorization\Application\Handler\GetUserRolesQueryHandler;
use App\DDD\Authorization\Application\Handler\RemoveRoleFromUserCommandHandler;
use App\DDD\Authorization\Application\Handler\SyncPermissionsToRoleCommandHandler;
use App\DDD\Authorization\Application\Handler\UpdatePermissionCommandHandler;
use App\DDD\Authorization\Application\Handler\UpdateRoleCommandHandler;
use App\DDD\Authorization\Application\Query\GetAllPermissionsQuery;
use App\DDD\Authorization\Application\Query\GetAllRolesQuery;
use App\DDD\Authorization\Application\Query\GetPermissionByIdQuery;
use App\DDD\Authorization\Application\Query\GetRoleByIdQuery;
use App\DDD\Authorization\Application\Query\GetUserPermissionsQuery;
use App\DDD\Authorization\Application\Query\GetUserRolesQuery;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Authorization\Infrastructure\Services\PermissionChecker;
use App\DDD\Holiday\Application\Command\ApproveHolidayRequestCommand;
use App\DDD\Holiday\Application\Command\CreateHolidayRequestCommand;
use App\DDD\Holiday\Application\Command\RejectHolidayRequestCommand;
use App\DDD\Holiday\Application\Handler\ApproveHolidayRequestCommandHandler;
use App\DDD\Holiday\Application\Handler\CreateHolidayRequestCommandHandler;
use App\DDD\Holiday\Application\Handler\GetApprovedHolidaysQueryHandler;
use App\DDD\Holiday\Application\Handler\GetPendingHolidaysQueryHandler;
use App\DDD\Holiday\Application\Handler\GetUserHolidaysQueryHandler;
use App\DDD\Holiday\Application\Handler\RejectHolidayRequestCommandHandler;
use App\DDD\Holiday\Application\Query\GetApprovedHolidaysQuery;
use App\DDD\Holiday\Application\Query\GetPendingHolidaysQuery;
use App\DDD\Holiday\Application\Query\GetUserHolidaysQuery;
use App\DDD\Notification\Application\Command\MarkNotificationAsReadCommand;
use App\DDD\Notification\Application\Handler\MarkNotificationAsReadCommandHandler;
use App\DDD\Notification\Application\NotificationService;
use App\DDD\Notification\Domain\Interface\NotificationRepositoryInterface;
use App\DDD\Notification\Domain\Policy\NotificationPolicy;
use App\DDD\Notification\Domain\Policy\NotificationPolicyInterface;
use App\DDD\Notification\Domain\Services\NotificationAuthorizationServiceInterface;
use App\DDD\Notification\Infrastructure\DatabaseNotifier;
use App\DDD\Notification\Infrastructure\Persistence\Eloquent\EloquentNotificationRepository;
use App\DDD\Notification\Infrastructure\Services\NotificationAuthorizationService;
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
use App\DDD\User\Application\Handler\GetAllUsersQueryHandler;
use App\DDD\User\Application\Handler\GetUserByIdQueryHandler;
use App\DDD\User\Application\Handler\GetUserDailyRegistrosQueryHandler;
use App\DDD\User\Application\Handler\GetUserTodayRegistrosQueryHandler;
use App\DDD\User\Application\Handler\ToggleUserActiveCommandHandler;
use App\DDD\User\Application\Query\GetAllUsersQuery;
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
        $this->app->bind(UserAuthorizationServiceInterface::class, UserAuthorizationService::class);

        // Register Authorization services
        $this->app->singleton(PermissionCheckerInterface::class, PermissionChecker::class);

        // Register Authentication services
        $this->app->bind(AuthenticationService::class, LaravelAuthenticationService::class);
        $this->app->bind(PasswordHashingService::class, LaravelPasswordHashingService::class);

        // Register Notification services
        $this->app->bind(NotificationRepositoryInterface::class, EloquentNotificationRepository::class);
        $this->app->bind(NotificationPolicyInterface::class, NotificationPolicy::class);
        $this->app->bind(NotificationAuthorizationServiceInterface::class, NotificationAuthorizationService::class);
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

        // Holiday Commands
        $tacticianBus->addHandler(CreateHolidayRequestCommand::class, CreateHolidayRequestCommandHandler::class);
        $tacticianBus->addHandler(ApproveHolidayRequestCommand::class, ApproveHolidayRequestCommandHandler::class);
        $tacticianBus->addHandler(RejectHolidayRequestCommand::class, RejectHolidayRequestCommandHandler::class);
        // Authorization Commands
        $tacticianBus->addHandler(AssignRoleToUserCommand::class, AssignRoleToUserCommandHandler::class);
        $tacticianBus->addHandler(RemoveRoleFromUserCommand::class, RemoveRoleFromUserCommandHandler::class);
        $tacticianBus->addHandler(CreateRoleCommand::class, CreateRoleCommandHandler::class);
        $tacticianBus->addHandler(UpdateRoleCommand::class, UpdateRoleCommandHandler::class);
        $tacticianBus->addHandler(DeleteRoleCommand::class, DeleteRoleCommandHandler::class);
        $tacticianBus->addHandler(SyncPermissionsToRoleCommand::class, SyncPermissionsToRoleCommandHandler::class);
        $tacticianBus->addHandler(CreatePermissionCommand::class, CreatePermissionCommandHandler::class);
        $tacticianBus->addHandler(UpdatePermissionCommand::class, UpdatePermissionCommandHandler::class);
        $tacticianBus->addHandler(DeletePermissionCommand::class, DeletePermissionCommandHandler::class);

        // Notification Commands
        $tacticianBus->addHandler(MarkNotificationAsReadCommand::class, MarkNotificationAsReadCommandHandler::class);
    }

    private function mapQueries(TacticianCommandBusInterface $tacticianBus): void
    {
        // User Queries
        $tacticianBus->addHandler(GetUserByIdQuery::class, GetUserByIdQueryHandler::class);
        $tacticianBus->addHandler(GetAllUsersQuery::class, GetAllUsersQueryHandler::class);
        $tacticianBus->addHandler(GetUserDailyRegistrosQuery::class, GetUserDailyRegistrosQueryHandler::class);
        $tacticianBus->addHandler(GetUserTodayRegistrosQuery::class, GetUserTodayRegistrosQueryHandler::class);

        // TimeTracking Queries
        $tacticianBus->addHandler(GetAccumulatedSecondsQuery::class, GetAccumulatedSecondsQueryHandler::class);
        $tacticianBus->addHandler(HasOpenTimeEntryQuery::class, HasOpenTimeEntryQueryHandler::class);

        // Authentication Queries
        $tacticianBus->addHandler(GetAuthenticatedUserQuery::class, GetAuthenticatedUserQueryHandler::class);

        // Holiday Queries
        $tacticianBus->addHandler(GetUserHolidaysQuery::class, GetUserHolidaysQueryHandler::class);
        $tacticianBus->addHandler(GetPendingHolidaysQuery::class, GetPendingHolidaysQueryHandler::class);
        $tacticianBus->addHandler(GetApprovedHolidaysQuery::class, GetApprovedHolidaysQueryHandler::class);
        // Authorization Queries
        $tacticianBus->addHandler(GetUserRolesQuery::class, GetUserRolesQueryHandler::class);
        $tacticianBus->addHandler(GetUserPermissionsQuery::class, GetUserPermissionsQueryHandler::class);
        $tacticianBus->addHandler(GetAllRolesQuery::class, GetAllRolesQueryHandler::class);
        $tacticianBus->addHandler(GetAllPermissionsQuery::class, GetAllPermissionsQueryHandler::class);
        $tacticianBus->addHandler(GetRoleByIdQuery::class, GetRoleByIdQueryHandler::class);
        $tacticianBus->addHandler(GetPermissionByIdQuery::class, GetPermissionByIdQueryHandler::class);
    }
}
