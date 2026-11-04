<?php

namespace Tests\Feature\Authorization;

use App\DDD\Authorization\Domain\Interface\UserPermissionsCheckerInterface;
use App\DDD\Authorization\Infrastructure\Service\Voter\AbstractPermissionVoter;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\User\Domain\Permission\UserPermission;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class UserPermissionsCheckerTest extends TestCase
{
    public const UNASSIGNED_PERMISSION = 'testing.unassigned_permission';

    private UserPermissionsCheckerInterface $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        Permission::create([
            'name' => 'Unassigned permission',
            'slug' => self::UNASSIGNED_PERMISSION,
            'bounded_context' => 'testing',
            'description' => 'Permiso sin asignar a ningún rol',
            'is_system' => false,
        ]);

        $this->checker = $this->app->make(UserPermissionsCheckerInterface::class);
    }

    public function testHasRolePermissionReturnsTrueWhenRoleHasPermission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');

        $this->assertTrue($this->checker->hasRolePermission($user->id, HolidayPermission::Request->value));
    }

    public function testHasRolePermissionReturnsFalseWhenRoleLacksPermission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');

        $this->assertFalse($this->checker->hasRolePermission($user->id, UserPermission::Delete->value));
    }

    public function testHasRolePermissionReturnsFalseForUserWithoutRoles(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($this->checker->hasRolePermission($user->id, HolidayPermission::Request->value));
    }

    public function testHasRolePermissionReturnsTrueWhenPermissionComesFromAnyRole(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');
        $user->assignRole('supervisor');

        $this->assertTrue($this->checker->hasRolePermission($user->id, HolidayPermission::Approve->value));
    }

    public function testHasRolePermissionReturnsFalseForUnknownPermission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->assertFalse($this->checker->hasRolePermission($user->id, 'nonexistent.permission'));
    }

    public function testHasRolePermissionDoesNotBypassSuperAdmin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->assertFalse($this->checker->hasRolePermission($user->id, self::UNASSIGNED_PERMISSION));
    }

    public function testHasRolePermissionReturnsTrueForSuperAdminWithAssignedPermission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->assertTrue($this->checker->hasRolePermission($user->id, UserPermission::Delete->value));
    }

    public function testIsSuperAdminReturnsTrueForSuperAdmin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->assertTrue($this->checker->isSuperAdmin($user->id));
    }

    public function testIsSuperAdminReturnsFalseForOtherRoles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertFalse($this->checker->isSuperAdmin($user->id));
    }

    public function testPermissionVoterGrantsSuperAdminWithoutAssignedPermission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->assertTrue($this->makeVoter()->vote($user->id, self::UNASSIGNED_PERMISSION)->isGranted());
    }

    public function testPermissionVoterDeniesRegularUserWithoutAssignedPermission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('employee');

        $this->assertTrue($this->makeVoter()->vote($user->id, self::UNASSIGNED_PERMISSION)->isDenied());
    }

    private function makeVoter(): AbstractPermissionVoter
    {
        return new class($this->checker) extends AbstractPermissionVoter {
            /**
             * @return string[]
             */
            public function supportedAttributes(): array
            {
                return [UserPermissionsCheckerTest::UNASSIGNED_PERMISSION];
            }
        };
    }
}
