<?php

namespace Tests\Unit\User\Application;

use App\DDD\User\Application\GetUserByIdUseCase;
use App\DDD\User\Domain\User;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\UserRepositoryInterface;
use App\DDD\User\Domain\exceptions\UserNotFoundException;
use PHPUnit\Framework\TestCase;
use Mockery;

class GetUserByIdUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private GetUserByIdUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new GetUserByIdUseCase($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_returns_user_as_array_when_user_exists(): void
    {
        $userId = '123';
        $userIdVO = new UserId($userId);
        $user = User::fromPrimitives(
            123,
            '123e4567-e89b-12d3-a456-426614174000',
            'test@example.com',
            'Test User',
            true
        );

        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with(Mockery::on(function ($arg) use ($userIdVO) {
                return $arg->getValue() === $userIdVO->getValue();
            }))
            ->andReturn($user);

        $result = $this->useCase->execute($userId);

        $this->assertIsArray($result);
        $this->assertEquals(123, $result['id']);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $result['uuid']);
        $this->assertEquals('test@example.com', $result['email']);
        $this->assertEquals('Test User', $result['name']);
        $this->assertTrue($result['is_active']);
    }

    public function test_it_throws_exception_when_user_not_found(): void
    {
        $userId = '999';
        $userIdVO = new UserId($userId);

        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with(Mockery::on(function ($arg) use ($userIdVO) {
                return $arg->getValue() === $userIdVO->getValue();
            }))
            ->andReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("User {$userId} not found");

        $this->useCase->execute($userId);
    }

    public function test_it_returns_user_with_all_required_fields(): void
    {
        $userId = '456';
        $userIdVO = new UserId($userId);
        $user = User::fromPrimitives(
            456,
            '223e4567-e89b-12d3-a456-426614174001',
            'another@example.com',
            'Another User',
            false
        );

        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with(Mockery::on(function ($arg) use ($userIdVO) {
                return $arg->getValue() === $userIdVO->getValue();
            }))
            ->andReturn($user);

        $result = $this->useCase->execute($userId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('is_active', $result);
        $this->assertEquals(456, $result['id']);
        $this->assertEquals('223e4567-e89b-12d3-a456-426614174001', $result['uuid']);
        $this->assertEquals('another@example.com', $result['email']);
        $this->assertEquals('Another User', $result['name']);
        $this->assertFalse($result['is_active']);
    }
}

