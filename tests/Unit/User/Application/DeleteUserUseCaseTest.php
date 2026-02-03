<?php

namespace Tests\Unit\User\Application;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Shared\Domain\Event\EventBusInterface;
use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Application\Handler\DeleteUserCommandHandler;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use Tests\TestCase;

class DeleteUserUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;

    private AuthorizationServiceInterface $authorizationService;

    private EventBusInterface $eventBus;

    private DeleteUserCommandHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = app(UserRepositoryInterface::class);
        $this->authorizationService = app(AuthorizationServiceInterface::class);
        $this->eventBus = app(EventBusInterface::class);
        $this->handler = new DeleteUserCommandHandler(
            $this->userRepository,
            $this->authorizationService,
            $this->eventBus
        );
    }

    public function test_it_deletes_a_user_successfully(): void
    {
        // Seed roles if not present
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Create admin user with super_admin role
        $adminUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);
        $adminUser->assignRole('super_admin');

        // Create target user to delete
        $eloquentUser = \App\Models\User::create([
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);

        $userId = $eloquentUser->id;

        $command = DeleteUserCommand::create($adminUser->id, $userId);
        $this->handler->handle($command);

        // Verify user was deleted
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_it_throws_exception_when_user_not_found(): void
    {
        // Seed roles if not present
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $adminUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);
        $adminUser->assignRole('super_admin');

        $userId = 999;

        $this->expectException(UserNotFoundException::class);

        $command = DeleteUserCommand::create($adminUser->id, $userId);
        $this->handler->handle($command);
    }

    public function test_it_throws_exception_when_delete_fails(): void
    {
        // Seed roles if not present
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $adminUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);
        $adminUser->assignRole('super_admin');

        // Try to delete another admin - should fail authorization
        $targetAdminUser = \App\Models\User::create([
            'uuid' => '123e4567-e89b-12d3-a456-426614174001',
            'name' => 'Another Admin',
            'email' => 'admin2@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);
        $targetAdminUser->assignRole('super_admin');

        $this->expectException(UnauthorizedException::class);

        $command = DeleteUserCommand::create($adminUser->id, $targetAdminUser->id);
        $this->handler->handle($command);
    }
}
