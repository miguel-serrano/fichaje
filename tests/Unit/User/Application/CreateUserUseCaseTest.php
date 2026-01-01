<?php

namespace Tests\Unit\User\Application;

use App\DDD\User\Application\Command\CreateUserCommand;
use App\DDD\User\Application\Handler\CreateUserCommandHandler;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserCreationValidator;
use App\DDD\User\Domain\Services\UserExistValidator;
use App\DDD\User\Domain\ValueObjects\Email;
use Mockery;
use PHPUnit\Framework\TestCase;

class CreateUserUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;

    private UserCreationValidator $userCreationValidator;

    private UserExistValidator $userExistValidator;

    private CreateUserCommandHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->userCreationValidator = Mockery::mock(UserCreationValidator::class);
        $this->userExistValidator = Mockery::mock(UserExistValidator::class);
        $this->handler = new CreateUserCommandHandler(
            $this->userRepository,
            $this->userCreationValidator,
            $this->userExistValidator
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_creates_a_user_successfully(): void
    {
        $email = 'test@example.com';
        $name = 'Test User';
        $emailVO = new Email($email);

        $savedUser = User::fromPrimitives(
            1,
            '123e4567-e89b-12d3-a456-426614174000',
            $email,
            $name,
            true
        );

        $this->userExistValidator
            ->shouldReceive('validate')
            ->once()
            ->with(Mockery::on(function ($arg) use ($emailVO) {
                return $arg->getValue() === $emailVO->getValue();
            }))
            ->andReturnNull();

        $this->userCreationValidator
            ->shouldReceive('validate')
            ->once()
            ->andReturnNull();

        $this->userRepository
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function ($arg) use ($email, $name) {
                return $arg->email()->getValue() === $email
                    && $arg->name() === $name;
            }))
            ->andReturn($savedUser);

        $command = new CreateUserCommand($email, $name);
        $result = $this->handler->handle($command);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->email()->getValue());
        $this->assertEquals($name, $result->name());
        $this->assertTrue($result->isActive());
    }

    public function test_it_throws_exception_when_email_already_exists(): void
    {
        $email = 'existing@example.com';
        $name = 'Test User';
        $emailVO = new Email($email);

        $this->userExistValidator
            ->shouldReceive('validate')
            ->once()
            ->with(Mockery::on(function ($arg) use ($emailVO) {
                return $arg->getValue() === $emailVO->getValue();
            }))
            ->andThrow(new \App\DDD\User\Domain\Exceptions\UserAlreadyExistsException($emailVO->getValue()));

        $this->userCreationValidator
            ->shouldNotReceive('validate');

        $this->userRepository
            ->shouldNotReceive('save');

        $this->expectException(\App\DDD\User\Domain\Exceptions\UserAlreadyExistsException::class);
        $this->expectExceptionMessage("User with email 'existing@example.com' already exists.");

        $command = new CreateUserCommand($email, $name);
        $this->handler->handle($command);
    }
}
