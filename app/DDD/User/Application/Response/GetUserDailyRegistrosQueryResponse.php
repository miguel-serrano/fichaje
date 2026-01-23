<?php

namespace App\DDD\User\Application\Response;

use App\DDD\Shared\Domain\Service\TimeFormatter;
use Illuminate\Support\Collection;

class GetUserDailyRegistrosQueryResponse
{
    /** @var array<string, array{fecha: string, fecha_formateada: string, registros: array<mixed>, total_segundos: int, total_formateado: string, tiene_abierto: bool}> */
    private array $registrosPorDia = [];

    /**
     * @param array{cerrados: Collection<int, \stdClass>, abiertos: Collection<int, \stdClass>} $entries
     */
    public function __construct(array $entries)
    {
        $this->processRegistrosCerrados($entries['cerrados']);
        $this->processRegistrosAbiertos($entries['abiertos']);
        $this->sortByDateDescending();
    }

    /**
     * @return array{registros: array<int, array{fecha: string, fecha_formateada: string, registros: array, total_segundos: int, total_formateado: string, tiene_abierto: bool}>, total_mes_actual: array{segundos: int, formateado: string, mes: string}}
     */
    public function response(): array
    {
        return [
            'registros' => array_values($this->registrosPorDia),
            'total_mes_actual' => $this->calculateMonthlyTotal(),
        ];
    }

    /**
     * @param Collection<int, \stdClass> $registrosCerrados
     */
    private function processRegistrosCerrados(Collection $registrosCerrados): void
    {
        foreach ($registrosCerrados as $registro) {
            $fecha = date('Y-m-d', $registro->entrada);
            $segundosTrabajados = $registro->salida - $registro->entrada;

            $this->ensureDayExists($fecha);

            $this->registrosPorDia[$fecha]['registros'][] = [
                'entrada' => date('H:i:s', $registro->entrada),
                'salida' => date('H:i:s', $registro->salida),
                'duracion' => TimeFormatter::formatTime($segundosTrabajados),
                'abierto' => false,
            ];

            $this->registrosPorDia[$fecha]['total_segundos'] += $segundosTrabajados;
            $this->registrosPorDia[$fecha]['total_formateado'] = TimeFormatter::formatTime(
                $this->registrosPorDia[$fecha]['total_segundos']
            );
        }
    }

    /**
     * @param Collection<int, \stdClass> $registrosAbiertos
     */
    private function processRegistrosAbiertos(Collection $registrosAbiertos): void
    {
        foreach ($registrosAbiertos as $registro) {
            $fecha = date('Y-m-d', $registro->entrada);
            $segundosTrabajados = time() - $registro->entrada;

            $this->ensureDayExists($fecha);

            $this->registrosPorDia[$fecha]['registros'][] = [
                'entrada' => date('H:i:s', $registro->entrada),
                'salida' => null,
                'duracion' => TimeFormatter::formatTime($segundosTrabajados),
                'abierto' => true,
                'entrada_timestamp' => $registro->entrada,
            ];

            $this->registrosPorDia[$fecha]['total_segundos'] += $segundosTrabajados;
            $this->registrosPorDia[$fecha]['total_formateado'] = TimeFormatter::formatTime(
                $this->registrosPorDia[$fecha]['total_segundos']
            );
            $this->registrosPorDia[$fecha]['tiene_abierto'] = true;
        }
    }

    private function ensureDayExists(string $fecha): void
    {
        if (!isset($this->registrosPorDia[$fecha])) {
            $this->registrosPorDia[$fecha] = [
                'fecha' => $fecha,
                'fecha_formateada' => date('d/m/Y', strtotime($fecha)),
                'registros' => [],
                'total_segundos' => 0,
                'total_formateado' => '00:00:00',
                'tiene_abierto' => false,
            ];
        }
    }

    private function sortByDateDescending(): void
    {
        krsort($this->registrosPorDia);
    }

    /**
     * @return array{segundos: int, formateado: string, mes: string}
     */
    private function calculateMonthlyTotal(): array
    {
        $totalSegundosMes = 0;
        $mesActual = date('Y-m');

        foreach ($this->registrosPorDia as $dia) {
            if (str_starts_with($dia['fecha'], $mesActual)) {
                $totalSegundosMes += $dia['total_segundos'];
            }
        }

        return [
            'segundos' => $totalSegundosMes,
            'formateado' => TimeFormatter::formatTime($totalSegundosMes),
            'mes' => TimeFormatter::formatMonth($mesActual),
        ];
    }
}
