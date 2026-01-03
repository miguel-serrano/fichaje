<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\TimeTracking\Services\TimeTrackingService;
use App\DDD\User\Application\Command\GetUserDailyRegistrosQuery;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class GetUserDailyRegistrosQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TimeTrackingService $timeTrackingService, // Injected directly
    ) {}

    public function handle(GetUserDailyRegistrosQuery $query): array
    {
        $userId = $query->getUserId();

        // Obtener todos los registros del usuario agrupados por día
        $registros = DB::table('time_entries')
            ->where('user_id', $userId)
            ->whereNotNull('salida') // Solo registros completos
            ->orderBy('entrada', 'desc')
            ->get();

        $registrosPorDia = [];

        foreach ($registros as $registro) {
            $fecha = date('Y-m-d', strtotime($registro->entrada));
            $entrada = new \DateTime($registro->entrada);
            $salida = new \DateTime($registro->salida);
            $duracion = $salida->diff($entrada);

            // Convertir duración a segundos para facilitar cálculos
            $segundosTrabajados = ($duracion->h * 3600) + ($duracion->i * 60) + $duracion->s;

            if (! isset($registrosPorDia[$fecha])) {
                $registrosPorDia[$fecha] = [
                    'fecha' => $fecha,
                    'fecha_formateada' => date('d/m/Y', strtotime($fecha)),
                    'registros' => [],
                    'total_segundos' => 0,
                    'total_formateado' => '00:00:00',
                ];
            }

            $registrosPorDia[$fecha]['registros'][] = [
                'entrada' => date('H:i:s', strtotime($registro->entrada)),
                'salida' => date('H:i:s', strtotime($registro->salida)),
                'duracion' => $this->formatearTiempo($segundosTrabajados),
            ];

            $registrosPorDia[$fecha]['total_segundos'] += $segundosTrabajados;
            $registrosPorDia[$fecha]['total_formateado'] = $this->formatearTiempo($registrosPorDia[$fecha]['total_segundos']);
        }

        // Calcular total del mes actual
        $totalSegundosMes = 0;
        $mesActual = date('Y-m');

        foreach ($registrosPorDia as $dia) {
            if (strpos($dia['fecha'], $mesActual) === 0) {
                $totalSegundosMes += $dia['total_segundos'];
            }
        }

        return [
            'registros' => array_values($registrosPorDia),
            'total_mes_actual' => [
                'segundos' => $totalSegundosMes,
                'formateado' => $this->formatearTiempo($totalSegundosMes),
                'mes' => $this->formatearMes(date('Y-m')),
            ],
        ];
    }

    private function formatearTiempo(int $segundos): string
    {
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segundosRestantes = $segundos % 60;

        return str_pad($horas, 2, '0', STR_PAD_LEFT).':'.
               str_pad($minutos, 2, '0', STR_PAD_LEFT).':'.
               str_pad($segundosRestantes, 2, '0', STR_PAD_LEFT);
    }

    private function formatearMes(string $yearMonth): string
    {
        $meses = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
            '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
            '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
        ];

        [$year, $month] = explode('-', $yearMonth);

        return $meses[$month].' '.$year;
    }
}
