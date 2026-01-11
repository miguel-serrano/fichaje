<?php

namespace Tests\Unit\RegistroHorario\Services;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Exceptions\NoOpenTimeEntryException;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Uuid;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\TestCase;

class RegistroHorarioServiceTest extends TestCase
{
    private UserRepositoryInterface $userRepository;

    private TimeEntryRepositoryInterface $timeEntryRepository;

    private PermissionCheckerInterface $permissionChecker;

    private TimeTrackingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->timeEntryRepository = Mockery::mock(TimeEntryRepositoryInterface::class);
        $this->permissionChecker = Mockery::mock(PermissionCheckerInterface::class);

        // Por defecto, los usuarios no son super_admin
        $this->permissionChecker->shouldReceive('isSuperAdmin')->andReturn(false)->byDefault();

        $this->service = new TimeTrackingService(
            $this->userRepository,
            $this->timeEntryRepository,
            $this->permissionChecker
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
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

    public function test_it_fichas_entrada_successfully(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);
        $user = $this->createUserAggregate($userUuidValue);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $savedUser = null;
        $this->userRepository
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (User $arg) use (&$savedUser) {
                $savedUser = $arg;

                return count($arg->timeEntries()) === 1 && $arg->timeEntries()[0]->isOpen();
            }))
            ->andReturn($user);

        $this->service->clockIn($userUuidValue);

        $this->assertNotNull($savedUser, 'User should have been saved.');
        $this->assertCount(1, $savedUser->timeEntries());
        $this->assertTrue($savedUser->timeEntries()[0]->isOpen());
    }

    public function test_it_throws_exception_on_fichar_entrada_if_user_not_found(): void
    {
        $userUuidValue = Str::uuid()->toString();
        $userUuid = new Uuid($userUuidValue);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Usuario no encontrado.');

        $this->service->clockIn($userUuidValue);
    }

    public function test_it_fichas_salida_successfully(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);
        $openRegistro = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => Carbon::now()->subHour()->toDateTimeString(),
            'salida' => null,
        ];
        $user = $this->createUserAggregate($userUuidValue, [$openRegistro]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $savedUser = null;
        $this->userRepository
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (User $arg) use (&$savedUser) {
                $savedUser = $arg;

                return count($arg->timeEntries()) === 1 && ! $arg->timeEntries()[0]->isOpen();
            }))
            ->andReturn($user);

        $this->service->clockOut($userUuidValue);

        $this->assertNotNull($savedUser, 'User should have been saved.');
        $this->assertCount(1, $savedUser->timeEntries());
        $this->assertFalse($savedUser->timeEntries()[0]->isOpen());
        $this->assertNotNull($savedUser->timeEntries()[0]->endTime());
    }

    public function test_it_throws_exception_on_fichar_salida_if_no_open_registro(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);
        $user = $this->createUserAggregate($userUuidValue, [
            [
                'id' => 1,
                'user_id' => 1,
                'entrada' => Carbon::now()->subHours(2)->toDateTimeString(),
                'salida' => Carbon::now()->subHour()->toDateTimeString(),
            ],
        ]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $this->expectException(NoOpenTimeEntryException::class);
        $this->expectExceptionMessage('No open time entry exists to close.');

        $this->service->clockOut($userUuidValue);
    }

    public function test_it_calculates_segundos_acumulados_successfully(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);

        $entrada1 = Carbon::now()->startOfDay()->addHours(9);
        $salida1 = Carbon::now()->startOfDay()->addHours(13);
        $registro1 = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => $entrada1->toDateTimeString(),
            'salida' => $salida1->toDateTimeString(),
        ];

        $entrada2 = Carbon::now()->startOfDay()->addHours(14);
        $salida2 = Carbon::now()->startOfDay()->addHours(18);
        $registro2 = [
            'id' => 2,
            'user_id' => 1,
            'entrada' => $entrada2->toDateTimeString(),
            'salida' => $salida2->toDateTimeString(),
        ];

        $user = $this->createUserAggregate($userUuidValue, [$registro1, $registro2]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $result = $this->service->getAccumulatedSeconds($userUuidValue);

        $expected = (4 * 3600) + (4 * 3600);
        $this->assertEquals($expected, $result);
    }

    public function test_it_calculates_segundos_acumulados_returns_zero_when_no_registros_for_today(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);

        $entrada1 = Carbon::yesterday()->startOfDay()->addHours(9);
        $salida1 = Carbon::yesterday()->startOfDay()->addHours(13);
        $registro1 = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => $entrada1->toDateTimeString(),
            'salida' => $salida1->toDateTimeString(),
        ];

        $user = $this->createUserAggregate($userUuidValue, [$registro1]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $result = $this->service->getAccumulatedSeconds($userUuidValue);

        $this->assertEquals(0, $result);
    }

    public function test_it_calculates_segundos_acumulados_includes_open_registros(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);

        $entrada1 = Carbon::now()->startOfDay()->addHours(9);
        $salida1 = Carbon::now()->startOfDay()->addHours(13);
        $registro1 = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => $entrada1->toDateTimeString(),
            'salida' => $salida1->toDateTimeString(),
        ];

        // Open registro started 1 hour ago
        $entrada2 = Carbon::now()->subHour();
        $registro2 = [
            'id' => 2,
            'user_id' => 1,
            'entrada' => $entrada2->toDateTimeString(),
            'salida' => null,
        ];

        $user = $this->createUserAggregate($userUuidValue, [$registro1, $registro2]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $result = $this->service->getAccumulatedSeconds($userUuidValue);

        // Should include closed registro (4h) + open registro (~1h)
        // Allow some tolerance for execution time
        $closedSeconds = 4 * 3600;
        $openSeconds = 3600; // approximately 1 hour
        $tolerance = 5; // 5 seconds tolerance

        $this->assertGreaterThanOrEqual($closedSeconds + $openSeconds - $tolerance, $result);
        $this->assertLessThanOrEqual($closedSeconds + $openSeconds + $tolerance, $result);
    }

    public function test_has_open_registro_returns_true_if_open_exists(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);

        $openRegistro = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => Carbon::now()->subHour()->toDateTimeString(),
            'salida' => null,
        ];
        $user = $this->createUserAggregate($userUuidValue, [$openRegistro]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $result = $this->service->hasOpenTimeEntry($userUuidValue);
        $this->assertTrue($result);
    }

    public function test_has_open_registro_returns_false_if_no_open_exists(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);

        $closedRegistro = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => Carbon::now()->subHours(2)->toDateTimeString(),
            'salida' => Carbon::now()->subHour()->toDateTimeString(),
        ];
        $user = $this->createUserAggregate($userUuidValue, [$closedRegistro]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $result = $this->service->hasOpenTimeEntry($userUuidValue);
        $this->assertFalse($result);
    }

    public function test_it_fichar_salida_with_registro_id_successfully(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $registroId = 1;
        $userUuid = new Uuid($userUuidValue);
        $openRegistro = [
            'id' => $registroId,
            'user_id' => 1,
            'entrada' => Carbon::now()->subHour()->toDateTimeString(),
            'salida' => null,
        ];
        $user = $this->createUserAggregate($userUuidValue, [$openRegistro]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $savedUser = null;
        $this->userRepository
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (User $arg) use (&$savedUser, $registroId) {
                $savedUser = $arg;
                $closedEntry = collect($arg->timeEntries())->first(function ($reg) use ($registroId) {
                    return $reg->id()->value() === $registroId;
                });

                return $closedEntry && ! $closedEntry->isOpen();
            }))
            ->andReturn($user);

        $this->service->clockOut($userUuidValue, $registroId);

        $this->assertNotNull($savedUser, 'User should have been saved.');
        $closedEntry = collect($savedUser->timeEntries())->first(function ($reg) use ($registroId) {
            return $reg->id()->value() === $registroId;
        });
        $this->assertNotNull($closedEntry);
        $this->assertFalse($closedEntry->isOpen());
        $this->assertNotNull($closedEntry->endTime());
    }

    public function test_it_throws_exception_on_fichar_salida_with_registro_id_if_user_not_found(): void
    {
        $userUuidValue = Str::uuid()->toString();
        $registroId = 1;
        $userUuid = new Uuid($userUuidValue);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Usuario no encontrado.');

        $this->service->clockOut($userUuidValue, $registroId);
    }

    public function test_it_throws_exception_on_fichar_salida_with_registro_id_if_entry_not_found(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $registroId = 999;
        $userUuid = new Uuid($userUuidValue);
        $user = $this->createUserAggregate($userUuidValue, [
            [
                'id' => 1,
                'user_id' => 1,
                'entrada' => Carbon::now()->subHour()->toDateTimeString(),
                'salida' => null,
            ],
        ]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $this->userRepository->shouldNotReceive('save');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Registro horario no encontrado.');

        $this->service->clockOut($userUuidValue, $registroId);
    }

    public function test_it_throws_exception_on_fichar_salida_with_registro_id_if_entry_already_closed(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $registroId = 1;
        $userUuid = new Uuid($userUuidValue);
        $closedRegistro = [
            'id' => $registroId,
            'user_id' => 1,
            'entrada' => Carbon::now()->subHours(2)->toDateTimeString(),
            'salida' => Carbon::now()->subHour()->toDateTimeString(),
        ];
        $user = $this->createUserAggregate($userUuidValue, [$closedRegistro]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->value() === $userUuid->value();
            }))
            ->andReturn($user);

        $this->userRepository->shouldNotReceive('save');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El registro horario ya está cerrado.');

        $this->service->clockOut($userUuidValue, $registroId);
    }
}
