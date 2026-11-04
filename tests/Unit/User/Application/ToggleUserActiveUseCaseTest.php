<?php

namespace Tests\Unit\User\Application;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Authorization\Domain\Exceptions\AccessDeniedException;
use App\DDD\Shared\Domain\Event\EventBusInterface;
use App\DDD\User\Application\Command\ToggleUserActiveCommand;
use App\DDD\User\Application\Handler\ToggleUserActiveCommandHandler;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class ToggleUserActiveUseCaseTest extends TestCase
{
    private ToggleUserActiveCommandHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $this->handler = new ToggleUserActiveCommandHandler(
            app(UserRepositoryInterface::class),
            app(AuthorizationServiceInterface::class),
            app(EventBusInterface::class),
        );
    }

    public function testSuperAdminTogglesAUser(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');
        $target = User::factory()->create(['is_active' => true]);
        $target->assignRole('employee');

        $isActive = $this->handler->handle(ToggleUserActiveCommand::create($superAdmin->id, $target->id));

        $this->assertFalse($isActive);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'is_active' => false]);
    }

    public function testAdminWithTogglePermissionTogglesAUser(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $target = User::factory()->create(['is_active' => false]);
        $target->assignRole('employee');

        $isActive = $this->handler->handle(ToggleUserActiveCommand::create($admin->id, $target->id));

        $this->assertTrue($isActive);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'is_active' => true]);
    }

    public function testAdminCannotToggleASuperAdmin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdmin->assignRole('super_admin');

        $this->assertToggleDenied($admin->id, $superAdmin->id, true);
    }

    public function testAdminCannotToggleThemselves(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $this->assertToggleDenied($admin->id, $admin->id, true);
    }

    public function testSuperAdminCannotToggleThemselves(): void
    {
        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdmin->assignRole('super_admin');

        $this->assertToggleDenied($superAdmin->id, $superAdmin->id, true);
    }

    public function testUserWithoutTogglePermissionCannotToggleAUser(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('employee');
        $target = User::factory()->create(['is_active' => true]);
        $target->assignRole('employee');

        $this->assertToggleDenied($employee->id, $target->id, true);
    }

    private function assertToggleDenied(int $authenticatedUserId, int $targetUserId, bool $expectedIsActive): void
    {
        try {
            $this->handler->handle(ToggleUserActiveCommand::create($authenticatedUserId, $targetUserId));
            $this->fail('Expected AccessDeniedException was not thrown');
        } catch (AccessDeniedException) {
            $this->assertDatabaseHas('users', ['id' => $targetUserId, 'is_active' => $expectedIsActive]);
        }
    }
}
