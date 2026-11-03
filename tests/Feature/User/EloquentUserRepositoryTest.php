<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\DDD\User\Domain\Entity\User as DomainUser;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class EloquentUserRepositoryTest extends TestCase
{
    private UserRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->repository = $this->app->make(UserRepositoryInterface::class);
    }

    public function test_user_has_role_returns_true_when_user_has_the_role(): void
    {
        $superAdmin = User::factory()->admin()->create();

        $this->assertTrue($this->repository->userHasRole(new UserId($superAdmin->id), 'super_admin'));
    }

    public function test_user_has_role_returns_false_when_user_has_another_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertFalse($this->repository->userHasRole(new UserId($admin->id), 'super_admin'));
    }

    public function test_user_has_role_returns_false_when_user_has_no_roles(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($this->repository->userHasRole(new UserId($user->id), 'super_admin'));
    }

    public function test_user_has_role_returns_false_for_unknown_role(): void
    {
        $superAdmin = User::factory()->admin()->create();

        $this->assertFalse($this->repository->userHasRole(new UserId($superAdmin->id), 'unknown_role'));
    }

    public function test_user_has_role_returns_false_for_non_existing_user(): void
    {
        $this->assertFalse($this->repository->userHasRole(new UserId(PHP_INT_MAX), 'super_admin'));
    }

    public function test_find_by_role_returns_only_users_with_that_role(): void
    {
        $superAdmin = User::factory()->admin()->create();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $employee = User::factory()->create();

        $ids = array_map(
            fn (DomainUser $user): int => $user->id()->value(),
            $this->repository->findByRole('super_admin'),
        );

        $this->assertContains($superAdmin->id, $ids);
        $this->assertNotContains($admin->id, $ids);
        $this->assertNotContains($employee->id, $ids);
    }

    public function test_find_by_role_returns_each_user_once_and_loads_all_their_roles(): void
    {
        $user = User::factory()->admin()->create();
        $user->assignRole('admin');

        $found = array_values(array_filter(
            $this->repository->findByRole('super_admin'),
            fn (DomainUser $domainUser): bool => $domainUser->id()->value() === $user->id,
        ));

        $this->assertCount(1, $found);
        $this->assertEqualsCanonicalizing(['super_admin', 'admin'], $found[0]->roleSlugs());
    }

    public function test_find_by_role_returns_empty_array_for_unknown_role(): void
    {
        $this->assertSame([], $this->repository->findByRole('unknown_role'));
    }
}
