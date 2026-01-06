<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\Shared\Domain\Service\TimeFormatter;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\User\Application\Query\GetAllUsersWithTimeQuery;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use Illuminate\Support\Facades\Log;
use Throwable;

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
                $tiempoFormateado = TimeFormatter::formatTime($segundos);

                $userData = $user->toArray();
                $userData['tiempo_acumulado'] = $tiempoFormateado;
                $usersWithTime[] = $userData;
            } catch (Throwable $e) {
                Log::warning('Failed to get accumulated time for user', [
                    'user_uuid' => $user->uuid()->value(),
                    'error' => $e->getMessage(),
                ]);

                $userData = $user->toArray();
                $userData['tiempo_acumulado'] = '00:00:00';
                $usersWithTime[] = $userData;
            }
        }

        return $usersWithTime;
    }
}
