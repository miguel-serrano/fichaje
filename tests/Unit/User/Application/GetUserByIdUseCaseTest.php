<?php

namespace Tests\Unit\User\Application;

use App\DDD\User\Application\Command\GetUserByIdQuery;
use App\DDD\User\Application\Handler\GetUserByIdQueryHandler;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\exceptions\UserNotFoundException;
use PHPUnit\Framework\TestCase;
use Mockery;

class GetUserByIdUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private GetUserByIdQueryHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->handler = new GetUserByIdQueryHandler($this->userRepository);
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

        $query = new GetUserByIdQuery($userId);
        $result = $this->handler->handle($query);

        $this->assertInstanceOf(\App\DDD\User\Domain\Entity\User::class, $result);
        $this->assertEquals(123, $result->id()->getValue());
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $result->uuid()->getValue());
        $this->assertEquals('test@example.com', $result->email()->getValue());
        $this->assertEquals('Test User', $result->name());
        $this->assertTrue($result->isActive());
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
        $this->expectExceptionMessage("User not found");

        $query = new GetUserByIdQuery($userId);
        $this->handler->handle($query);
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

        $query = new GetUserByIdQuery($userId);
        $result = $this->handler->handle($query);

        $this->assertInstanceOf(\App\DDD\User\Domain\Entity\User::class, $result);
        $this->assertNotNull($result->id());
        $this->assertNotNull($result->uuid());
        $this->assertNotNull($result->email());
        $this->assertNotNull($result->name());
        $this->assertIsBool($result->isActive());
        $this->assertEquals(456, $result->id()->getValue());
        $this->assertEquals('223e4567-e89b-12d3-a456-426614174001', $result->uuid()->getValue());
        $this->assertEquals('another@example.com', $result->email()->getValue());
        $this->assertEquals('Another User', $result->name());
        $this->assertFalse($result->isActive());
    }
}

