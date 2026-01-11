<?php

namespace Tests\Unit\User\Application;

use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Application\Handler\DeleteUserCommandHandler;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use App\DDD\User\Infrastructure\Services\UserAuthorizationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeleteUserUseCaseTest extends TestCase
{
    use DatabaseTransactions;

    private UserRepositoryInterface $userRepository;

    private UserAuthorizationServiceInterface $authorizationService;

    private DeleteUserCommandHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new EloquentUserRepository;
        $this->authorizationService = app(UserAuthorizationService::class);
        $this->handler = new DeleteUserCommandHandler(
            $this->userRepository,
            $this->authorizationService
        );
    }

    public function test_it_deletes_a_user_successfully(): void
    {
        // Create admin user
        $adminUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
            'is_admin' => true,
        ]);

        // Create target user to delete
        $eloquentUser = \App\Models\User::create([
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);

        $userId = $eloquentUser->id;

        $command = new DeleteUserCommand($adminUser->id, $userId);
        $this->handler->handle($command);

        // Verify user was deleted
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_it_throws_exception_when_user_not_found(): void
    {
        $adminUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
            'is_admin' => true,
        ]);

        $userId = 999;

        $this->expectException(UserNotFoundException::class);

        $command = new DeleteUserCommand($adminUser->id, $userId);
        $this->handler->handle($command);
    }

    public function test_it_throws_exception_when_delete_fails(): void
    {
        $adminUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
            'is_admin' => true,
        ]);

        // Try to delete another admin - should fail authorization
        $targetAdminUser = \App\Models\User::create([
            'uuid' => '123e4567-e89b-12d3-a456-426614174001',
            'name' => 'Another Admin',
            'email' => 'admin2@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
            'is_admin' => true,
        ]);

        $this->expectException(UnauthorizedException::class);

        $command = new DeleteUserCommand($adminUser->id, $targetAdminUser->id);
        $this->handler->handle($command);
    }
}
