<?php

namespace Tests\Unit\User\Application;

use App\DDD\User\Application\GetUserByIdUseCase;
use App\DDD\User\Domain\Email;
use App\DDD\User\Domain\User;
use App\DDD\User\Domain\UserId;
use App\DDD\User\Domain\exceptions\UserNotFoundException;
use App\DDD\User\Domain\UserRepositoryInterface;
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

    public function test_it_returns_user_when_found(): void
    {
        $userId = '123e4567-e89b-12d3-a456-426614174000';
        $user = User::fromPrimitives(
            $userId,
            'test@example.com',
            'Test User',
            true
        );

        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with(Mockery::on(function ($arg) use ($userId) {
                return $arg instanceof UserId && $arg->getValue() === $userId;
            }))
            ->andReturn($user);

        $result = $this->useCase->execute($userId);

        $this->assertIsArray($result);
        $this->assertEquals([
            'id' => $userId,
            'email' => 'test@example.com',
            'name' => 'Test User',
            'is_active' => true
        ], $result);
    }

    public function test_it_throws_exception_when_user_not_found(): void
    {
        $userId = '123e4567-e89b-12d3-a456-426614174000';

        $this->userRepository
            ->shouldReceive('findById')
            ->once()
            ->with(Mockery::on(function ($arg) use ($userId) {
                return $arg instanceof UserId && $arg->getValue() === $userId;
            }))
            ->andReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("User {$userId} not found");

        $this->useCase->execute($userId);
    }
}

