<?php

namespace Tests\Unit\User\Application;

use App\DDD\User\Application\GetAllUsersUseCase;
use App\DDD\User\Domain\Email;
use App\DDD\User\Domain\User;
use App\DDD\User\Domain\UserId;
use App\DDD\User\Domain\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Mockery;

class GetAllUsersUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private GetAllUsersUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new GetAllUsersUseCase($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_returns_all_users_as_array(): void
    {
        $user1 = User::fromPrimitives(
            '123e4567-e89b-12d3-a456-426614174000',
            'user1@example.com',
            'User One',
            true
        );

        $user2 = User::fromPrimitives(
            '123e4567-e89b-12d3-a456-426614174001',
            'user2@example.com',
            'User Two',
            true
        );

        $users = [$user1, $user2];

        $this->userRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn($users);

        $result = $this->useCase->execute();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals([
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'email' => 'user1@example.com',
            'name' => 'User One',
            'is_active' => true
        ], $result[0]);
        $this->assertEquals([
            'id' => '123e4567-e89b-12d3-a456-426614174001',
            'email' => 'user2@example.com',
            'name' => 'User Two',
            'is_active' => true
        ], $result[1]);
    }

    public function test_it_returns_empty_array_when_no_users_exist(): void
    {
        $this->userRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([]);

        $result = $this->useCase->execute();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}

