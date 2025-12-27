<?php

namespace Tests\Unit\User\Application;

use App\DDD\User\Application\CreateUserUseCase;
use App\DDD\User\Domain\Email;
use App\DDD\User\Domain\User;
use App\DDD\User\Domain\UserId;
use App\DDD\User\Domain\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Mockery;

class CreateUserUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private CreateUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new CreateUserUseCase($this->userRepository);
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

        $this->userRepository
            ->shouldReceive('existsByEmail')
            ->once()
            ->with(Mockery::on(function ($arg) use ($emailVO) {
                return $arg->getValue() === $emailVO->getValue();
            }))
            ->andReturn(false);

        $this->userRepository
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function ($arg) use ($email, $name) {
                return $arg->email()->getValue() === $email 
                    && $arg->name() === $name;
            }))
            ->andReturn($savedUser);

        $result = $this->useCase->execute($email, $name);

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

        $this->userRepository
            ->shouldReceive('existsByEmail')
            ->once()
            ->with(Mockery::on(function ($arg) use ($emailVO) {
                return $arg->getValue() === $emailVO->getValue();
            }))
            ->andReturn(true);

        $this->userRepository
            ->shouldNotReceive('save');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email already exists');

        $this->useCase->execute($email, $name);
    }
}

