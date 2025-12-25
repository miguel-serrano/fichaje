<?php

namespace Tests\Unit\User\Application;

use App\DDD\User\Application\DeleteUserUseCase;
use App\DDD\User\Domain\Email;
use App\DDD\User\Domain\User;
use App\DDD\User\Domain\UserId;
use App\DDD\User\Domain\exceptions\UserNotFoundException;
use App\DDD\User\Domain\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Mockery;

class DeleteUserUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private DeleteUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new DeleteUserUseCase($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_deletes_user_successfully(): void
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

        $this->userRepository
            ->shouldReceive('delete')
            ->once()
            ->with(Mockery::on(function ($arg) use ($userId) {
                return $arg instanceof UserId && $arg->getValue() === $userId;
            }))
            ->andReturn(true);

        $this->useCase->execute($userId);
        
        // If we get here without exception, the test passes
        $this->assertTrue(true);
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

        $this->userRepository
            ->shouldNotReceive('delete');

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("User {$userId} not found");

        $this->useCase->execute($userId);
    }

    public function test_it_throws_exception_when_delete_fails(): void
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

        $this->userRepository
            ->shouldReceive('delete')
            ->once()
            ->with(Mockery::on(function ($arg) use ($userId) {
                return $arg instanceof UserId && $arg->getValue() === $userId;
            }))
            ->andReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Failed to delete user {$userId}");

        $this->useCase->execute($userId);
    }
}

