<?php

namespace Tests\Unit\RegistroHorario\Services;

use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RegistroHorarioServiceTest extends TestCase
{
    private UserRepositoryInterface $userRepository;

    private TimeEntryRepositoryInterface $timeEntryRepository;

    private LoggerInterface $logger;

    private TimeTrackingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = \Mockery::mock(UserRepositoryInterface::class);
        $this->timeEntryRepository = \Mockery::mock(TimeEntryRepositoryInterface::class);
        $this->logger = \Mockery::mock(LoggerInterface::class);

        // Permitir cualquier llamada a logger
        $this->logger->shouldReceive('info')->byDefault();

        $this->service = TimeTrackingService::create(
            $this->userRepository,
            $this->timeEntryRepository,
            $this->logger
        );
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    private function createUserAggregate(string $uuidValue, array $registros = []): User
    {
        return User::fromPrimitives(
            1, // id
            $uuidValue,
            'test@example.com',
            'Test User',
            true,
            $registros
        );
    }

    private function expectClockingWindowFor(User $user): void
    {
        $this->timeEntryRepository
            ->shouldReceive('findClockingWindowByUserId')
            ->once()
            ->with(\Mockery::on(fn (UserId $userId): bool => $userId->value() === $user->id()->value()))
            ->andReturn($user->timeEntries());
    }

    public function testItCalculatesSegundosAcumuladosSuccessfully(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);

        $todayMidnight = strtotime('today 00:00:00');
        $entrada1 = $todayMidnight + (9 * 3600);  // 09:00
        $salida1 = $todayMidnight + (13 * 3600);  // 13:00
        $registro1 = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => $entrada1,
            'salida' => $salida1,
        ];

        $entrada2 = $todayMidnight + (14 * 3600);  // 14:00
        $salida2 = $todayMidnight + (18 * 3600);  // 18:00
        $registro2 = [
            'id' => 2,
            'user_id' => 1,
            'entrada' => $entrada2,
            'salida' => $salida2,
        ];

        $user = $this->createUserAggregate($userUuidValue, [$registro1, $registro2]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(\Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $this->expectClockingWindowFor($user);

        $result = $this->service->getAccumulatedSeconds($userUuidValue);

        $expected = (4 * 3600) + (4 * 3600);
        $this->assertEquals($expected, $result);
    }

    public function testItCalculatesSegundosAcumuladosReturnsZeroWhenNoRegistrosForToday(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);

        $yesterdayMidnight = strtotime('yesterday 00:00:00');
        $entrada1 = $yesterdayMidnight + (9 * 3600);  // 09:00
        $salida1 = $yesterdayMidnight + (13 * 3600);  // 13:00
        $registro1 = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => $entrada1,
            'salida' => $salida1,
        ];

        $user = $this->createUserAggregate($userUuidValue, [$registro1]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(\Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $this->expectClockingWindowFor($user);

        $result = $this->service->getAccumulatedSeconds($userUuidValue);

        $this->assertEquals(0, $result);
    }

    public function testItCalculatesSegundosAcumuladosIncludesOpenRegistros(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);

        $todayMidnight = strtotime('today 00:00:00');
        $entrada1 = $todayMidnight + (9 * 3600);  // 09:00
        $salida1 = $todayMidnight + (13 * 3600);  // 13:00
        $registro1 = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => $entrada1,
            'salida' => $salida1,
        ];

        // Open registro started 1 hour ago
        $entrada2 = time() - 3600;
        $registro2 = [
            'id' => 2,
            'user_id' => 1,
            'entrada' => $entrada2,
            'salida' => null,
        ];

        $user = $this->createUserAggregate($userUuidValue, [$registro1, $registro2]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(\Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $this->expectClockingWindowFor($user);

        $result = $this->service->getAccumulatedSeconds($userUuidValue);

        // Should include closed registro (4h) + open registro (~1h)
        // Allow some tolerance for execution time
        $closedSeconds = 4 * 3600;
        $openSeconds = 3600; // approximately 1 hour
        $tolerance = 5; // 5 seconds tolerance

        $this->assertGreaterThanOrEqual($closedSeconds + $openSeconds - $tolerance, $result);
        $this->assertLessThanOrEqual($closedSeconds + $openSeconds + $tolerance, $result);
    }

    public function testHasOpenRegistroReturnsTrueIfOpenExists(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);

        $openRegistro = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => time() - 3600,
            'salida' => null,
        ];
        $user = $this->createUserAggregate($userUuidValue, [$openRegistro]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(\Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $this->expectClockingWindowFor($user);

        $result = $this->service->hasOpenTimeEntry($userUuidValue);
        $this->assertTrue($result);
    }

    public function testHasOpenRegistroReturnsFalseIfNoOpenExists(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);

        $closedRegistro = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => time() - 7200,
            'salida' => time() - 3600,
        ];
        $user = $this->createUserAggregate($userUuidValue, [$closedRegistro]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(\Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $this->expectClockingWindowFor($user);

        $result = $this->service->hasOpenTimeEntry($userUuidValue);
        $this->assertFalse($result);
    }
}
