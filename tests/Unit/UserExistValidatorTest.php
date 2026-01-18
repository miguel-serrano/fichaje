<?php

namespace Tests\Unit;

use App\DDD\User\Domain\Exceptions\UserAlreadyExistsException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserExistValidator;
use App\DDD\User\Domain\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

class UserExistValidatorTest extends TestCase
{
    private UserRepositoryInterface $mockRepository;

    private UserExistValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = $this->createMock(UserRepositoryInterface::class);
        $this->validator = new UserExistValidator($this->mockRepository);
    }

    public function test_validates_successfully_when_user_does_not_exist(): void
    {
        $email = new Email('newuser@example.com');

        $this->mockRepository
            ->expects($this->once())
            ->method('existsByEmail')
            ->with($email)
            ->willReturn(false);

        // Should not throw any exception
        $this->validator->validate($email);
        $this->assertTrue(true); // If we reach here, validation passed
    }

    public function test_throws_exception_when_user_already_exists(): void
    {
        $email = new Email('existing@example.com');

        $this->mockRepository
            ->expects($this->once())
            ->method('existsByEmail')
            ->with($email)
            ->willReturn(true);

        $this->expectException(UserAlreadyExistsException::class);
        $this->expectExceptionMessage("User with email 'existing@example.com' already exists.");

        $this->validator->validate($email);
    }

    public function test_exists_returns_true_when_user_exists(): void
    {
        $email = new Email('existing@example.com');

        $this->mockRepository
            ->expects($this->once())
            ->method('existsByEmail')
            ->with($email)
            ->willReturn(true);

        $this->assertTrue($this->validator->exists($email));
    }

    public function test_exists_returns_false_when_user_does_not_exist(): void
    {
        $email = new Email('newuser@example.com');

        $this->mockRepository
            ->expects($this->once())
            ->method('existsByEmail')
            ->with($email)
            ->willReturn(false);

        $this->assertFalse($this->validator->exists($email));
    }
}
