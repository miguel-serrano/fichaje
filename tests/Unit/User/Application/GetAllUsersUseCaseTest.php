<?php

namespace Tests\Unit\User\Application;

use App\DDD\User\Application\GetAllUsersUseCase;
use App\DDD\User\Domain\User;
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
        $users = [
            User::fromPrimitives(
                1,
                '123e4567-e89b-12d3-a456-426614174000',
                'user1@example.com',
                'User One',
                true
            ),
            User::fromPrimitives(
                2,
                '223e4567-e89b-12d3-a456-426614174001',
                'user2@example.com',
                'User Two',
                true
            ),
        ];

        $this->userRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn($users);

        $result = $this->useCase->execute();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('user1@example.com', $result[0]['email']);
        $this->assertEquals('User One', $result[0]['name']);
        $this->assertEquals('user2@example.com', $result[1]['email']);
        $this->assertEquals('User Two', $result[1]['name']);
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

    public function test_it_converts_users_to_array_correctly(): void
    {
        $user = User::fromPrimitives(
            1,
            '123e4567-e89b-12d3-a456-426614174000',
            'test@example.com',
            'Test User',
            true
        );

        $this->userRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([$user]);

        $result = $this->useCase->execute();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('uuid', $result[0]);
        $this->assertArrayHasKey('email', $result[0]);
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('is_active', $result[0]);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $result[0]['uuid']);
        $this->assertEquals('test@example.com', $result[0]['email']);
        $this->assertEquals('Test User', $result[0]['name']);
        $this->assertTrue($result[0]['is_active']);
    }
}

