<?php

namespace App\DDD\User\Application\Response;

use App\DDD\Shared\Domain\Service\TimeFormatter;
use Illuminate\Support\Collection;

class GetUserDailyRegistrosQueryResponse
{
    /** @var array<string, array{fecha: string, fecha_formateada: string, registros: array<mixed>, total_segundos: int, total_formateado: string, tiene_abierto: bool}> */
    private array $entriesByDay = [];

    /**
     * @param array{cerrados: Collection<int, \stdClass>, abiertos: Collection<int, \stdClass>} $entries
     */
    public function __construct(array $entries)
    {
        $this->processClosedEntries($entries['cerrados']);
        $this->processOpenEntries($entries['abiertos']);
        $this->sortByDateDescending();
    }

    /**
     * @return array{registros: array<int, array{fecha: string, fecha_formateada: string, registros: array, total_segundos: int, total_formateado: string, tiene_abierto: bool}>, total_mes_actual: array{segundos: int, formateado: string, mes: string}}
     */
    public function response(): array
    {
        return [
            'registros' => array_values($this->entriesByDay),
            'total_mes_actual' => $this->calculateMonthlyTotal(),
        ];
    }

    /**
     * @param Collection<int, \stdClass> $closedEntries
     */
    private function processClosedEntries(Collection $closedEntries): void
    {
        foreach ($closedEntries as $entry) {
            $date = date('Y-m-d', $entry->entrada);
            $workedSeconds = $entry->salida - $entry->entrada;

            $this->ensureDayExists($date);

            $this->entriesByDay[$date]['registros'][] = [
                'entrada' => date('H:i:s', $entry->entrada),
                'salida' => date('H:i:s', $entry->salida),
                'duracion' => TimeFormatter::formatTime($workedSeconds),
                'abierto' => false,
            ];

            $this->entriesByDay[$date]['total_segundos'] += $workedSeconds;
            $this->entriesByDay[$date]['total_formateado'] = TimeFormatter::formatTime(
                $this->entriesByDay[$date]['total_segundos']
            );
        }
    }

    /**
     * @param Collection<int, \stdClass> $openEntries
     */
    private function processOpenEntries(Collection $openEntries): void
    {
        foreach ($openEntries as $entry) {
            $date = date('Y-m-d', $entry->entrada);
            $workedSeconds = time() - $entry->entrada;

            $this->ensureDayExists($date);

            $this->entriesByDay[$date]['registros'][] = [
                'entrada' => date('H:i:s', $entry->entrada),
                'salida' => null,
                'duracion' => TimeFormatter::formatTime($workedSeconds),
                'abierto' => true,
                'entrada_timestamp' => $entry->entrada,
            ];

            $this->entriesByDay[$date]['total_segundos'] += $workedSeconds;
            $this->entriesByDay[$date]['total_formateado'] = TimeFormatter::formatTime(
                $this->entriesByDay[$date]['total_segundos']
            );
            $this->entriesByDay[$date]['tiene_abierto'] = true;
        }
    }

    private function ensureDayExists(string $date): void
    {
        if (!isset($this->entriesByDay[$date])) {
            $this->entriesByDay[$date] = [
                'fecha' => $date,
                'fecha_formateada' => date('d/m/Y', strtotime($date)),
                'registros' => [],
                'total_segundos' => 0,
                'total_formateado' => '00:00:00',
                'tiene_abierto' => false,
            ];
        }
    }

    private function sortByDateDescending(): void
    {
        krsort($this->entriesByDay);
    }

    /**
     * @return array{segundos: int, formateado: string, mes: string}
     */
    private function calculateMonthlyTotal(): array
    {
        $totalSecondsMonth = 0;
        $currentMonth = date('Y-m');

        foreach ($this->entriesByDay as $day) {
            if (str_starts_with($day['fecha'], $currentMonth)) {
                $totalSecondsMonth += $day['total_segundos'];
            }
        }

        return [
            'segundos' => $totalSecondsMonth,
            'formateado' => TimeFormatter::formatTime($totalSecondsMonth),
            'mes' => TimeFormatter::formatMonth($currentMonth),
        ];
    }
}
