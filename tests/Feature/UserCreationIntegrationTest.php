<?php

namespace Tests\Feature;

use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\User\Application\Command\CreateUserCommand;
use App\DDD\User\Domain\Exceptions\MaxUsersLimitExceededException;
use App\DDD\User\Domain\Services\UserCreationValidator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class UserCreationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_user_successfully_through_command_bus(): void
    {
        Config::set('users.limits.max_users', 10);

        /** @var CommandBusInterface $commandBus */
        $commandBus = app(CommandBusInterface::class);

        $command = new CreateUserCommand('test@example.com', 'Test User');
        $result = $commandBus->dispatch($command);

        $this->assertEquals('test@example.com', $result->email()->getValue());
        $this->assertEquals('Test User', $result->name());

        // Verify user was saved to database
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'is_active' => true,
        ]);
    }

    public function test_throws_exception_when_max_users_limit_exceeded(): void
    {
        Config::set('users.limits.max_users', 2);

        // Create 2 users to reach the limit
        User::factory()->count(2)->create();

        /** @var CommandBusInterface $commandBus */
        $commandBus = app(CommandBusInterface::class);

        $this->expectException(MaxUsersLimitExceededException::class);
        $this->expectExceptionMessage('Cannot create more users. Maximum limit of 2 users has been reached. Current count: 2.');

        $command = new CreateUserCommand('test@example.com', 'Test User');
        $commandBus->dispatch($command);
    }

    public function test_domain_validator_works_independently(): void
    {
        Config::set('users.limits.max_users', 3);

        // Create 2 users
        User::factory()->count(2)->create();

        /** @var UserCreationValidator $validator */
        $validator = app(UserCreationValidator::class);

        // Should pass validation
        $this->assertTrue($validator->canCreateUser());
        $this->assertEquals(2, $validator->getCurrentUserCount());
        $this->assertEquals(3, $validator->getMaxUsersLimit());

        // Should not throw exception
        $validator->validate();

        // Create one more user to reach the limit
        User::factory()->create();

        // Now should fail validation
        $this->assertFalse($validator->canCreateUser());
        $this->assertEquals(3, $validator->getCurrentUserCount());

        $this->expectException(MaxUsersLimitExceededException::class);
        $validator->validate();
    }

    public function test_email_validation_happens_before_user_limit_validation(): void
    {
        Config::set('users.limits.max_users', 1);

        // Create a user with specific email
        User::factory()->create(['email' => 'existing@example.com']);

        /** @var CommandBusInterface $commandBus */
        $commandBus = app(CommandBusInterface::class);

        // Try to create user with same email - should fail with email validation, not user limit
        $this->expectException(\App\DDD\User\Domain\Exceptions\UserAlreadyExistsException::class);
        $this->expectExceptionMessage("User with email 'existing@example.com' already exists.");

        $command = new CreateUserCommand('existing@example.com', 'Another User');
        $commandBus->dispatch($command);
    }

    public function test_user_creation_with_zero_limit_always_fails(): void
    {
        Config::set('users.limits.max_users', 0);

        /** @var UserCreationValidator $validator */
        $validator = app(UserCreationValidator::class);

        $this->assertFalse($validator->canCreateUser());
        $this->assertEquals(0, $validator->getMaxUsersLimit());

        $this->expectException(MaxUsersLimitExceededException::class);
        $this->expectExceptionMessage('Cannot create more users. Maximum limit of 0 users has been reached. Current count: 0.');

        $validator->validate();
    }
}
