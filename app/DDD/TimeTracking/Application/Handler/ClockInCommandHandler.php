<?php

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Application\Command\ClockInCommand;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class ClockInCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private PermissionCheckerInterface $permissionChecker,
        private TimeTrackingService $service,
    ) {
    }

    public function handle(ClockInCommand $command): void
    {
        // 1. Obtener usuario (ÚNICA consulta a BD)
        $user = $this->userRepository->findByUuidOrFail($command->userUuid);

        // 2. Verificar que el usuario está activo (lógica de dominio)
        $user->ensureIsActive();

        // 3. Verificar permisos de autorización
        $this->permissionChecker->assertHasPermission(
            $user,
            TimeTrackingPermission::ClockIn->value
        );

        // 4. Validar reglas de negocio (entrada abierta, límite diario)
        $this->service->ensureCanClockIn($user);

        // 5. Crear entidad de dominio y persistir
        $timeEntry = TimeEntry::create($user->id());
        $this->timeEntryRepository->save($timeEntry);
    }
}
