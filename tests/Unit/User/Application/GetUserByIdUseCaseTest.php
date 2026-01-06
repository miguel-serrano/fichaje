<?php

namespace Tests\Unit\User\Application;

use App\DDD\User\Application\Handler\GetUserByIdQueryHandler;
use App\DDD\User\Application\Query\GetUserByIdQuery;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use App\DDD\User\Infrastructure\Services\UserAuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUserByIdUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private UserRepositoryInterface $userRepository;

    private UserAuthorizationServiceInterface $authorizationService;

    private GetUserByIdQueryHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new EloquentUserRepository;
        $this->authorizationService = app(UserAuthorizationService::class);
        $this->handler = new GetUserByIdQueryHandler(
            $this->userRepository,
            $this->authorizationService
        );
    }

    public function test_it_returns_user_as_array_when_user_exists(): void
    {
        $adminUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
            'is_admin' => true,
        ]);

        $targetUser = \App\Models\User::create([
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);

        $query = new GetUserByIdQuery($adminUser->id, $targetUser->id);
        $result = $this->handler->handle($query);

        $this->assertInstanceOf(\App\DDD\User\Domain\Entity\User::class, $result);
        $this->assertEquals($targetUser->id, $result->id()->value());
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $result->uuid()->value());
        $this->assertEquals('test@example.com', $result->email()->value());
        $this->assertEquals('Test User', $result->name());
        $this->assertTrue($result->isActive());
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

        $query = new GetUserByIdQuery($adminUser->id, $userId);
        $this->handler->handle($query);
    }

    public function test_it_returns_user_with_all_required_fields(): void
    {
        $adminUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
            'is_admin' => true,
        ]);

        $targetUser = \App\Models\User::create([
            'uuid' => '223e4567-e89b-12d3-a456-426614174001',
            'name' => 'Another User',
            'email' => 'another@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => false,
        ]);

        $query = new GetUserByIdQuery($adminUser->id, $targetUser->id);
        $result = $this->handler->handle($query);

        $this->assertInstanceOf(\App\DDD\User\Domain\Entity\User::class, $result);
        $this->assertNotNull($result->id());
        $this->assertNotNull($result->uuid());
        $this->assertNotNull($result->email());
        $this->assertNotNull($result->name());
        $this->assertIsBool($result->isActive());
        $this->assertEquals($targetUser->id, $result->id()->value());
        $this->assertEquals('223e4567-e89b-12d3-a456-426614174001', $result->uuid()->value());
        $this->assertEquals('another@example.com', $result->email()->value());
        $this->assertEquals('Another User', $result->name());
        $this->assertFalse($result->isActive());
    }

    public function test_non_admin_can_view_themselves(): void
    {
        $regularUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);

        $query = new GetUserByIdQuery($regularUser->id, $regularUser->id);
        $result = $this->handler->handle($query);

        $this->assertInstanceOf(\App\DDD\User\Domain\Entity\User::class, $result);
        $this->assertEquals($regularUser->id, $result->id()->value());
    }

    public function test_non_admin_cannot_view_other_users(): void
    {
        $regularUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);

        $otherUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->expectException(UnauthorizedException::class);

        $query = new GetUserByIdQuery($regularUser->id, $otherUser->id);
        $this->handler->handle($query);
    }
}
