<?php

namespace Tests\Unit\RegistroHorario\Services;

use App\DDD\RegistroHorario\Services\RegistroHorarioService;
use App\DDD\RegistroHorario\Domain\RegistroHorarioRepositoryInterface;
use App\DDD\RegistroHorario\Domain\RegistroHorario;
use PHPUnit\Framework\TestCase;
use Mockery;

class RegistroHorarioServiceTest extends TestCase
{
    private RegistroHorarioRepositoryInterface $repository;
    private RegistroHorarioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(RegistroHorarioRepositoryInterface::class);
        $this->service = new RegistroHorarioService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_fichas_entrada_successfully(): void
    {
        $userUuid = '123e4567-e89b-12d3-a456-426614174000';
        $now = now();
        
        $registro = new RegistroHorario(1, $userUuid, $now, null);

        $this->repository
            ->shouldReceive('crearEntrada')
            ->once()
            ->with($userUuid, Mockery::type(\Illuminate\Support\Carbon::class))
            ->andReturn($registro);

        $result = $this->service->ficharEntrada($userUuid);

        $this->assertInstanceOf(RegistroHorario::class, $result);
        $this->assertEquals($userUuid, $result->userId);
        $this->assertEquals($now, $result->entrada);
        $this->assertNull($result->salida);
    }

    public function test_it_fichas_salida_successfully(): void
    {
        $userUuid = '123e4567-e89b-12d3-a456-426614174000';
        $entrada = now()->subHours(8);
        $salida = now();
        
        $registroAbierto = new RegistroHorario(1, $userUuid, $entrada, null);
        $registroCerrado = new RegistroHorario(1, $userUuid, $entrada, $salida);

        $this->repository
            ->shouldReceive('obtenerUltimoAbierto')
            ->once()
            ->with($userUuid)
            ->andReturn($registroAbierto);

        $this->repository
            ->shouldReceive('cerrarRegistro')
            ->once()
            ->with($registroAbierto->id, Mockery::type(\Illuminate\Support\Carbon::class))
            ->andReturn($registroCerrado);

        $result = $this->service->ficharSalida($userUuid);

        $this->assertInstanceOf(RegistroHorario::class, $result);
        $this->assertEquals($registroAbierto->id, $result->id);
        $this->assertEquals($entrada, $result->entrada);
        $this->assertEquals($salida, $result->salida);
    }

    public function test_it_throws_exception_when_fichar_salida_without_entrada(): void
    {
        $userUuid = '123e4567-e89b-12d3-a456-426614174000';

        $this->repository
            ->shouldReceive('obtenerUltimoAbierto')
            ->once()
            ->with($userUuid)
            ->andReturn(null);

        $this->repository
            ->shouldNotReceive('cerrarRegistro');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No hay registro de entrada abierto para cerrar');

        $this->service->ficharSalida($userUuid);
    }

    public function test_it_obtiene_ultimo_registro_successfully(): void
    {
        $userUuid = '123e4567-e89b-12d3-a456-426614174000';
        $entrada = now()->subHours(4);
        
        $registro = new RegistroHorario(1, $userUuid, $entrada, null);

        $this->repository
            ->shouldReceive('obtenerUltimoAbierto')
            ->once()
            ->with($userUuid)
            ->andReturn($registro);

        $result = $this->service->obtenerUltimoRegistro($userUuid);

        $this->assertInstanceOf(RegistroHorario::class, $result);
        $this->assertEquals($registro->id, $result->id);
        $this->assertEquals($userUuid, $result->userId);
        $this->assertEquals($entrada, $result->entrada);
    }

    public function test_it_obtiene_ultimo_registro_returns_null_when_no_registros(): void
    {
        $userUuid = '123e4567-e89b-12d3-a456-426614174000';

        $this->repository
            ->shouldReceive('obtenerUltimoAbierto')
            ->once()
            ->with($userUuid)
            ->andReturn(null);

        $result = $this->service->obtenerUltimoRegistro($userUuid);

        $this->assertNull($result);
    }

    public function test_it_calculates_segundos_acumulados_successfully(): void
    {
        $userUuid = '123e4567-e89b-12d3-a456-426614174000';
        $today = now()->toDateString();
        
        $entrada1 = now()->setTime(9, 0, 0);
        $salida1 = now()->setTime(13, 0, 0);
        $registro1 = new RegistroHorario(1, $userUuid, $entrada1->toDateTimeString(), $salida1->toDateTimeString());

        $entrada2 = now()->setTime(14, 0, 0);
        $salida2 = now()->setTime(18, 0, 0);
        $registro2 = new RegistroHorario(2, $userUuid, $entrada2->toDateTimeString(), $salida2->toDateTimeString());

        $registros = [$registro1, $registro2];

        $this->repository
            ->shouldReceive('obtenerRegistros')
            ->once()
            ->with($userUuid, $today)
            ->andReturn($registros);

        $result = $this->service->segundosAcumulados($userUuid);

        $this->assertIsInt($result);
        // 4 horas + 4 horas = 8 horas = 28800 segundos
        $expected = (4 * 3600) + (4 * 3600);
        $this->assertEquals($expected, $result);
    }

    public function test_it_calculates_segundos_acumulados_returns_zero_when_no_registros(): void
    {
        $userUuid = '123e4567-e89b-12d3-a456-426614174000';
        $today = now()->toDateString();

        $this->repository
            ->shouldReceive('obtenerRegistros')
            ->once()
            ->with($userUuid, $today)
            ->andReturn([]);

        $result = $this->service->segundosAcumulados($userUuid);

        $this->assertEquals(0, $result);
    }

    public function test_it_calculates_segundos_acumulados_ignores_registros_sin_salida(): void
    {
        $userUuid = '123e4567-e89b-12d3-a456-426614174000';
        $today = now()->toDateString();
        
        $entrada1 = now()->setTime(9, 0, 0);
        $salida1 = now()->setTime(13, 0, 0);
        $registro1 = new RegistroHorario(1, $userUuid, $entrada1->toDateTimeString(), $salida1->toDateTimeString());

        $entrada2 = now()->setTime(14, 0, 0);
        $registro2 = new RegistroHorario(2, $userUuid, $entrada2->toDateTimeString(), null);

        $registros = [$registro1, $registro2];

        $this->repository
            ->shouldReceive('obtenerRegistros')
            ->once()
            ->with($userUuid, $today)
            ->andReturn($registros);

        $result = $this->service->segundosAcumulados($userUuid);

        // Solo cuenta el registro1 (4 horas = 14400 segundos)
        // El registro2 sin salida debería retornar 0 en segundosTrabajados()
        $this->assertEquals(4 * 3600, $result);
    }
}

