<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\TimeTracking\Services\TimeTrackingService;
use App\DDD\User\Application\Command\GetAllUsersWithTimeQuery;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class GetAllUsersWithTimeQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TimeTrackingService $timeTrackingService, // Injected directly
    ) {}

    public function handle(GetAllUsersWithTimeQuery $query): array
    {
        $users = $this->userRepository->findAll();
        $usersWithTime = [];

        foreach ($users as $user) {
            try {
                $segundos = $this->timeTrackingService->getAccumulatedSeconds($user->uuid()->getValue());
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

        return str_pad($horas, 2, '0', STR_PAD_LEFT).':'.
               str_pad($minutos, 2, '0', STR_PAD_LEFT).':'.
               str_pad($segundosRestantes, 2, '0', STR_PAD_LEFT);
    }
}
