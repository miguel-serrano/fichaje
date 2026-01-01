<?php

namespace Tests\Unit\RegistroHorario\Services;

use App\DDD\RegistroHorario\Services\RegistroHorarioService;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\RegistroHorario\Domain\RegistroHorario;
use App\DDD\RegistroHorario\Domain\ValueObjects\RegistroHorarioId;
use PHPUnit\Framework\TestCase;
use Mockery;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str; // Import Str facade for UUID generation

class RegistroHorarioServiceTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private RegistroHorarioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->service = new RegistroHorarioService($this->userRepository);
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
                return $arg->getValue() === $userUuid->getValue();
            }))
            ->andReturn($user);

        // Capture the user passed to save
        $savedUser = null;
        $this->userRepository
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (User $arg) use (&$savedUser) {
                $savedUser = $arg; // Capture the argument
                return count($arg->registrosHorarios()) === 1 && $arg->registrosHorarios()[0]->isAbierto();
            }))
            ->andReturn($user); 

        $this->service->ficharEntrada($userUuidValue);

        // Assertions after the service call
        $this->assertNotNull($savedUser, "User should have been saved.");
        $this->assertCount(1, $savedUser->registrosHorarios());
        $this->assertTrue($savedUser->registrosHorarios()[0]->isAbierto());
    }

    public function test_it_throws_exception_on_fichar_entrada_if_user_not_found(): void
    {
        $userUuidValue = Str::uuid()->toString(); // Use a valid UUID format
        $userUuid = new Uuid($userUuidValue);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->getValue() === $userUuid->getValue();
            }))
            ->andReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Usuario no encontrado.');

        $this->service->ficharEntrada($userUuidValue);
    }

    public function test_it_fichas_salida_successfully(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);
        $openRegistro = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => Carbon::now()->subHour()->toDateTimeString(),
            'salida' => null
        ];
        $user = $this->createUserAggregate($userUuidValue, [$openRegistro]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->getValue() === $userUuid->getValue();
            }))
            ->andReturn($user);

        // Capture the user passed to save
        $savedUser = null;
        $this->userRepository
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (User $arg) use (&$savedUser) {
                $savedUser = $arg; // Capture the argument
                return count($arg->registrosHorarios()) === 1 && !$arg->registrosHorarios()[0]->isAbierto();
            }))
            ->andReturn($user);

        $this->service->ficharSalida($userUuidValue);

        // Assertions after the service call
        $this->assertNotNull($savedUser, "User should have been saved.");
        $this->assertCount(1, $savedUser->registrosHorarios());
        $this->assertFalse($savedUser->registrosHorarios()[0]->isAbierto());
        $this->assertNotNull($savedUser->registrosHorarios()[0]->salida());
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
                'salida' => Carbon::now()->subHour()->toDateTimeString()
            ]
        ]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->getValue() === $userUuid->getValue();
            }))
            ->andReturn($user);
        
        // Expect the exception from the User entity's ficharSalida method
        $this->expectException(Exception::class); 
        $this->expectExceptionMessage('No existe un registro de entrada abierto para cerrar.');

        $this->service->ficharSalida($userUuidValue);
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
            'salida' => $salida1->toDateTimeString()
        ];

        $entrada2 = Carbon::now()->startOfDay()->addHours(14);
        $salida2 = Carbon::now()->startOfDay()->addHours(18);
        $registro2 = [
            'id' => 2,
            'user_id' => 1,
            'entrada' => $entrada2->toDateTimeString(),
            'salida' => $salida2->toDateTimeString()
        ];
        
        $user = $this->createUserAggregate($userUuidValue, [$registro1, $registro2]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->getValue() === $userUuid->getValue();
            }))
            ->andReturn($user);

        $result = $this->service->segundosAcumulados($userUuidValue);

        $expected = (4 * 3600) + (4 * 3600); // 8 hours
        $this->assertEquals($expected, $result);
    }

    public function test_it_calculates_segundos_acumulados_returns_zero_when_no_registros_for_today(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);
        
        // Registros de ayer
        $entrada1 = Carbon::yesterday()->startOfDay()->addHours(9);
        $salida1 = Carbon::yesterday()->startOfDay()->addHours(13);
        $registro1 = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => $entrada1->toDateTimeString(),
            'salida' => $salida1->toDateTimeString()
        ];
        
        $user = $this->createUserAggregate($userUuidValue, [$registro1]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->getValue() === $userUuid->getValue();
            }))
            ->andReturn($user);

        $result = $this->service->segundosAcumulados($userUuidValue);

        $this->assertEquals(0, $result);
    }

    public function test_it_calculates_segundos_acumulados_ignores_registros_sin_salida(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);
        
        $entrada1 = Carbon::now()->startOfDay()->addHours(9);
        $salida1 = Carbon::now()->startOfDay()->addHours(13);
        $registro1 = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => $entrada1->toDateTimeString(),
            'salida' => $salida1->toDateTimeString()
        ];

        $entrada2 = Carbon::now()->startOfDay()->addHours(14);
        $registro2 = [ // Registro sin salida
            'id' => 2,
            'user_id' => 1,
            'entrada' => $entrada2->toDateTimeString(),
            'salida' => null
        ];

        $user = $this->createUserAggregate($userUuidValue, [$registro1, $registro2]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->getValue() === $userUuid->getValue();
            }))
            ->andReturn($user);

        $result = $this->service->segundosAcumulados($userUuidValue);

        $expected = (4 * 3600); // Only registro1 counts
        $this->assertEquals($expected, $result);
    }

    public function test_has_open_registro_returns_true_if_open_exists(): void
    {
        $userUuidValue = '123e4567-e89b-12d3-a456-426614174000';
        $userUuid = new Uuid($userUuidValue);
        
        $openRegistro = [
            'id' => 1,
            'user_id' => 1,
            'entrada' => Carbon::now()->subHour()->toDateTimeString(),
            'salida' => null
        ];
        $user = $this->createUserAggregate($userUuidValue, [$openRegistro]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->getValue() === $userUuid->getValue();
            }))
            ->andReturn($user);

        $result = $this->service->hasOpenRegistro($userUuidValue);
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
            'salida' => Carbon::now()->subHour()->toDateTimeString()
        ];
        $user = $this->createUserAggregate($userUuidValue, [$closedRegistro]);

        $this->userRepository
            ->shouldReceive('findByUuid')
            ->once()
            ->with(Mockery::on(function (Uuid $arg) use ($userUuid) {
                return $arg->getValue() === $userUuid->getValue();
            }))
            ->andReturn($user);

        $result = $this->service->hasOpenRegistro($userUuidValue);
        $this->assertFalse($result);
    }
}
