<?php

namespace Tests\Unit\User\Application;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Authorization\Domain\Exceptions\AccessDeniedException;
use App\DDD\Shared\Domain\Event\EventBusInterface;
use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Application\Handler\DeleteUserCommandHandler;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
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

    public function testItDeletesAUserSuccessfully(): void
    {
        // Seed roles if not present
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        // Create admin user with super_admin role
        $adminUser = User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);
        $adminUser->assignRole('super_admin');

        // Create target user to delete
        $eloquentUser = User::create([
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

    public function testItThrowsExceptionWhenUserNotFound(): void
    {
        // Seed roles if not present
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $adminUser = User::create([
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

    public function testItThrowsExceptionWhenDeleteFails(): void
    {
        // Seed roles if not present
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $adminUser = User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);
        $adminUser->assignRole('super_admin');

        // Try to delete another admin - should fail authorization
        $targetAdminUser = User::create([
            'uuid' => '123e4567-e89b-12d3-a456-426614174001',
            'name' => 'Another Admin',
            'email' => 'admin2@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);
        $targetAdminUser->assignRole('super_admin');

        $this->expectException(AccessDeniedException::class);

        $command = DeleteUserCommand::create($adminUser->id, $targetAdminUser->id);
        $this->handler->handle($command);
    }

    public function testAdminWithDeletePermissionDeletesAUser(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $target = User::factory()->create();
        $target->assignRole('employee');

        $this->handler->handle(DeleteUserCommand::create($admin->id, $target->id));

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function testAdminCannotDeleteASuperAdmin(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        try {
            $this->handler->handle(DeleteUserCommand::create($admin->id, $superAdmin->id));
            $this->fail('Expected AccessDeniedException was not thrown');
        } catch (AccessDeniedException) {
            $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
        }
    }

    public function testUserWithoutDeletePermissionCannotDeleteAUser(): void
    {
        $this->seedRolesAndPermissions();

        $employee = User::factory()->create();
        $employee->assignRole('employee');
        $target = User::factory()->create();
        $target->assignRole('employee');

        try {
            $this->handler->handle(DeleteUserCommand::create($employee->id, $target->id));
            $this->fail('Expected AccessDeniedException was not thrown');
        } catch (AccessDeniedException) {
            $this->assertDatabaseHas('users', ['id' => $target->id]);
        }
    }

    public function testAdminCannotDeleteThemselves(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        try {
            $this->handler->handle(DeleteUserCommand::create($admin->id, $admin->id));
            $this->fail('Expected AccessDeniedException was not thrown');
        } catch (AccessDeniedException) {
            $this->assertDatabaseHas('users', ['id' => $admin->id]);
        }
    }

    private function seedRolesAndPermissions(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);
    }
}
