<?php

namespace Tests\Unit\User\Application;

use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Application\Handler\DeleteUserCommandHandler;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use Mockery;
use PHPUnit\Framework\TestCase;

class DeleteUserUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;

    private DeleteUserCommandHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->handler = new DeleteUserCommandHandler($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_deletes_a_user_successfully(): void
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

        $this->userRepository
            ->shouldReceive('delete')
            ->once()
            ->with(Mockery::on(function ($arg) use ($userIdVO) {
                return $arg->getValue() === $userIdVO->getValue();
            }))
            ->andReturn(true);

        $command = new DeleteUserCommand($userId);
        $this->handler->handle($command);

        // Assert that the test completed without exceptions
        $this->assertTrue(true);
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

        $this->userRepository
            ->shouldNotReceive('delete');

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("User {$userId} not found");

        $command = new DeleteUserCommand($userId);
        $this->handler->handle($command);
    }

    public function test_it_throws_exception_when_delete_fails(): void
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

        $this->userRepository
            ->shouldReceive('delete')
            ->once()
            ->with(Mockery::on(function ($arg) use ($userIdVO) {
                return $arg->getValue() === $userIdVO->getValue();
            }))
            ->andReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Failed to delete user {$userId}");

        $command = new DeleteUserCommand($userId);
        $this->handler->handle($command);
    }
}
