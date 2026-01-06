<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\TimeTracking\Services\TimeTrackingService;
use App\DDD\User\Application\Query\GetAllUsersWithTimeQuery;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class GetAllUsersWithTimeQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TimeTrackingService $timeTrackingService,
        private UserAuthorizationServiceInterface $authorizationService
    ) {}

    public function handle(GetAllUsersWithTimeQuery $query): array
    {
        $authenticatedUserId = new UserId($query->authenticatedUserId);
        $authenticatedUser = $this->userRepository->findByIdOrFail($authenticatedUserId);

        $this->authorizationService->ensureCanList($authenticatedUser);

        $users = $this->userRepository->findAll();
        $usersWithTime = [];

        foreach ($users as $user) {
            try {
                $segundos = $this->timeTrackingService->getAccumulatedSeconds($user->uuid()->value());
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
