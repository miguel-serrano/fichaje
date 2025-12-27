<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\RegistroHorario\Services\RegistroHorarioService;
use App\DDD\User\Application\Command\GetAllUsersWithTimeQuery;
use App\DDD\RegistroHorario\Domain\RegistroHorarioRepositoryInterface;
use App\DDD\RegistroHorario\Infrastructure\Persistence\Eloquent\RegistroHorarioRepositoryEloquent;

class GetAllUsersWithTimeQueryHandler
{
    private RegistroHorarioService $registroHorarioService;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RegistroHorarioRepositoryInterface $registroHorarioRepository,
    ) {
        // TO DO: Esto debería inyectarse via DI en lugar de instanciar directamente
        //$repository = new RegistroHorarioRepositoryEloquent();

        
        $this->registroHorarioService = new RegistroHorarioService($registroHorarioRepository);
    }

    public function handle(GetAllUsersWithTimeQuery $query): array
    {
        $users = $this->userRepository->findAll();
        $usersWithTime = [];

        foreach ($users as $user) {
            try {
                $segundos = $this->registroHorarioService->segundosAcumulados($user->uuid()->getValue());
                $tiempoFormateado = $this->formatearTiempo($segundos);
                
                $userData = $user->toArray();
                $userData['tiempo_acumulado'] = $tiempoFormateado;
                $usersWithTime[] = $userData;
            } catch (\Exception $e) {
                $userData = $user->toArray();
                $userData['tiempo_acumulado'] = '00:00:00';
                $usersWithTime[] = $userData;
            }
        }

        return $usersWithTime;
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
