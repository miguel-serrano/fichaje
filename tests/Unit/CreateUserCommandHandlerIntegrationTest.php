<?php

namespace Tests\Unit;

use App\DDD\User\Application\Command\CreateUserCommand;
use App\DDD\User\Application\Handler\CreateUserCommandHandler;
use App\DDD\User\Domain\Exceptions\MaxUsersLimitExceededException;
use App\DDD\User\Domain\Exceptions\UserAlreadyExistsException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserCreationValidator;
use App\DDD\User\Domain\Services\UserExistValidator;
use App\DDD\User\Domain\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

class CreateUserCommandHandlerIntegrationTest extends TestCase
{
    private UserRepositoryInterface $mockRepository;

    private UserCreationValidator $userCreationValidator;

    private UserExistValidator $userExistValidator;

    private CreateUserCommandHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = $this->createMock(UserRepositoryInterface::class);
    }

    public function test_creates_user_successfully_when_under_limit(): void
    {
        // Setup: Under the limit
        $this->mockRepository
            ->expects($this->once())
            ->method('count')
            ->willReturn(5);

        $this->mockRepository
            ->expects($this->once())
            ->method('existsByEmail')
            ->willReturn(false);

        $this->mockRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn ($user) => $user);

        $this->userCreationValidator = new UserCreationValidator($this->mockRepository, 10);
        $this->userExistValidator = new UserExistValidator($this->mockRepository);
        $this->handler = new CreateUserCommandHandler(
            $this->mockRepository,
            $this->userCreationValidator,
            $this->userExistValidator
        );

        $command = new CreateUserCommand('test@example.com', 'Test User');
        $result = $this->handler->handle($command);

        $this->assertEquals('test@example.com', $result->email()->getValue());
        $this->assertEquals('Test User', $result->name());
    }

    public function test_throws_exception_when_max_users_limit_exceeded(): void
    {
        // Setup: At the limit
        $this->mockRepository
            ->expects($this->once())
            ->method('count')
            ->willReturn(10);

        $this->mockRepository
            ->expects($this->once())
            ->method('existsByEmail')
            ->willReturn(false);

        // save() should not be called because validation fails
        $this->mockRepository
            ->expects($this->never())
            ->method('save');

        $this->userCreationValidator = new UserCreationValidator($this->mockRepository, 10);
        $this->userExistValidator = new UserExistValidator($this->mockRepository);
        $this->handler = new CreateUserCommandHandler(
            $this->mockRepository,
            $this->userCreationValidator,
            $this->userExistValidator
        );

        $command = new CreateUserCommand('test@example.com', 'Test User');

        $this->expectException(MaxUsersLimitExceededException::class);
        $this->expectExceptionMessage('Cannot create more users. Maximum limit of 10 users has been reached. Current count: 10.');

        $this->handler->handle($command);
    }

    public function test_throws_exception_when_email_already_exists(): void
    {
        // Setup: Email exists
        $this->mockRepository
            ->expects($this->once())
            ->method('existsByEmail')
            ->willReturn(true);

        // count() and save() should not be called because email validation fails first
        $this->mockRepository
            ->expects($this->never())
            ->method('count');

        $this->mockRepository
            ->expects($this->never())
            ->method('save');

        $this->userCreationValidator = new UserCreationValidator($this->mockRepository, 10);
        $this->userExistValidator = new UserExistValidator($this->mockRepository);
        $this->handler = new CreateUserCommandHandler(
            $this->mockRepository,
            $this->userCreationValidator,
            $this->userExistValidator
        );

        $command = new CreateUserCommand('existing@example.com', 'Test User');

        $this->expectException(UserAlreadyExistsException::class);
        $this->expectExceptionMessage("User with email 'existing@example.com' already exists.");

        $this->handler->handle($command);
    }

    public function test_validates_email_first_then_user_limit(): void
    {
        // This test ensures the order of validations is correct
        $this->mockRepository
            ->expects($this->once())
            ->method('existsByEmail')
            ->willReturn(false);

        $this->mockRepository
            ->expects($this->once())
            ->method('count')
            ->willReturn(5);

        $this->mockRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn ($user) => $user);

        $this->userCreationValidator = new UserCreationValidator($this->mockRepository, 10);
        $this->userExistValidator = new UserExistValidator($this->mockRepository);
        $this->handler = new CreateUserCommandHandler(
            $this->mockRepository,
            $this->userCreationValidator,
            $this->userExistValidator
        );

        $command = new CreateUserCommand('new@example.com', 'New User');
        $result = $this->handler->handle($command);

        $this->assertNotNull($result);
        $this->assertEquals('new@example.com', $result->email()->getValue());
    }
}
