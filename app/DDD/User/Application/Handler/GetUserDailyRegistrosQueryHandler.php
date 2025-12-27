<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\GetUserDailyRegistrosQuery;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\RegistroHorario\Services\RegistroHorarioService;
use App\DDD\RegistroHorario\Domain\RegistroHorarioRepositoryInterface;
use Illuminate\Support\Facades\DB;

class GetUserDailyRegistrosQueryHandler
{
    private RegistroHorarioService $registroHorarioService;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RegistroHorarioRepositoryInterface $registroHorarioRepository,
    ) {
        $this->registroHorarioService = new RegistroHorarioService($registroHorarioRepository);
    }

    public function handle(GetUserDailyRegistrosQuery $query): array
    {
        $userId = $query->getUserId();
        
        // Obtener todos los registros del usuario agrupados por día
        $registros = DB::table('registro_horarios')
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
            
            if (!isset($registrosPorDia[$fecha])) {
                $registrosPorDia[$fecha] = [
                    'fecha' => $fecha,
                    'fecha_formateada' => date('d/m/Y', strtotime($fecha)),
                    'registros' => [],
                    'total_segundos' => 0,
                    'total_formateado' => '00:00:00'
                ];
            }
            
            $registrosPorDia[$fecha]['registros'][] = [
                'entrada' => date('H:i:s', strtotime($registro->entrada)),
                'salida' => date('H:i:s', strtotime($registro->salida)),
                'duracion' => $this->formatearTiempo($segundosTrabajados)
            ];
            
            $registrosPorDia[$fecha]['total_segundos'] += $segundosTrabajados;
            $registrosPorDia[$fecha]['total_formateado'] = $this->formatearTiempo($registrosPorDia[$fecha]['total_segundos']);
        }

        return array_values($registrosPorDia);
    }

    private function formatearTiempo(int $segundos): string
    {
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segundosRestantes = $segundos % 60;
        
        return str_pad($horas, 2, '0', STR_PAD_LEFT) . ':' . 
               str_pad($minutos, 2, '0', STR_PAD_LEFT) . ':' . 
               str_pad($segundosRestantes, 2, '0', STR_PAD_LEFT);
    }
}
