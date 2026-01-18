<?php

namespace Tests\Unit;

use App\DDD\User\Domain\Exceptions\MaxUsersLimitExceededException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserCreationValidator;
use PHPUnit\Framework\TestCase;

class UserCreationValidatorTest extends TestCase
{
    private UserRepositoryInterface $mockRepository;

    private UserCreationValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = $this->createMock(UserRepositoryInterface::class);
    }

    public function test_validates_user_creation_successfully_when_under_limit(): void
    {
        $this->mockRepository
            ->expects($this->once())
            ->method('count')
            ->willReturn(5);

        $this->validator = new UserCreationValidator($this->mockRepository, 10);

        // Should not throw any exception
        $this->validator->validate();
        $this->assertTrue(true); // If we reach here, validation passed
    }

    public function test_throws_exception_when_max_users_limit_exceeded(): void
    {
        $this->mockRepository
            ->expects($this->exactly(2))
            ->method('count')
            ->willReturn(10);

        $this->validator = new UserCreationValidator($this->mockRepository, 10);

        $this->expectException(MaxUsersLimitExceededException::class);
        $this->expectExceptionMessage('Cannot create more users. Maximum limit of 10 users has been reached. Current count: 10.');

        $this->validator->validate();
    }

    public function test_throws_exception_when_exactly_at_limit(): void
    {
        $this->mockRepository
            ->expects($this->exactly(2))
            ->method('count')
            ->willReturn(5);

        $this->validator = new UserCreationValidator($this->mockRepository, 5);

        $this->expectException(MaxUsersLimitExceededException::class);
        $this->expectExceptionMessage('Cannot create more users. Maximum limit of 5 users has been reached. Current count: 5.');

        $this->validator->validate();
    }

    public function test_can_create_user_returns_true_when_under_limit(): void
    {
        $this->mockRepository
            ->expects($this->once())
            ->method('count')
            ->willReturn(3);

        $this->validator = new UserCreationValidator($this->mockRepository, 10);

        $this->assertTrue($this->validator->canCreateUser());
    }

    public function test_can_create_user_returns_false_when_at_limit(): void
    {
        $this->mockRepository
            ->expects($this->once())
            ->method('count')
            ->willReturn(10);

        $this->validator = new UserCreationValidator($this->mockRepository, 10);

        $this->assertFalse($this->validator->canCreateUser());
    }

    public function test_can_create_user_returns_false_when_over_limit(): void
    {
        $this->mockRepository
            ->expects($this->once())
            ->method('count')
            ->willReturn(15);

        $this->validator = new UserCreationValidator($this->mockRepository, 10);

        $this->assertFalse($this->validator->canCreateUser());
    }


    public function test_validates_with_zero_limit_always_throws_exception(): void
    {
        $this->mockRepository
            ->expects($this->exactly(2))
            ->method('count')
            ->willReturn(0);

        $this->validator = new UserCreationValidator($this->mockRepository, 0);

        $this->expectException(MaxUsersLimitExceededException::class);
        $this->expectExceptionMessage('Cannot create more users. Maximum limit of 0 users has been reached. Current count: 0.');

        $this->validator->validate();
    }

    public function test_validates_with_negative_limit_always_throws_exception(): void
    {
        $this->mockRepository
            ->expects($this->exactly(2))
            ->method('count')
            ->willReturn(0);

        $this->validator = new UserCreationValidator($this->mockRepository, -1);

        $this->expectException(MaxUsersLimitExceededException::class);
        $this->expectExceptionMessage('Cannot create more users. Maximum limit of -1 users has been reached. Current count: 0.');

        $this->validator->validate();
    }
}
