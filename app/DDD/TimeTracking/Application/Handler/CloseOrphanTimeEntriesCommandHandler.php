<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\TimeTracking\Application\Command\CloseOrphanTimeEntriesCommand;
use App\DDD\TimeTracking\Application\Response\CloseOrphanTimeEntriesCommandResponse;
use App\DDD\TimeTracking\Application\Service\TimeTrackingNotifierService;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;

final class CloseOrphanTimeEntriesCommandHandler
{
    public function __construct(
        private TimeTrackingService $service,
        private TimeTrackingNotifierService $notifierService,
    ) {
    }

    public function handle(CloseOrphanTimeEntriesCommand $command): CloseOrphanTimeEntriesCommandResponse
    {
        // 1. Ejecutar lógica de negocio (servicio retorna datos para notificar)
        $closedByUser = $this->service->closeOrphanTimeEntries();

        // 2. Side effects (notificaciones)
        $this->notifierService->notifyOrphanEntriesClosed($closedByUser);

        return new CloseOrphanTimeEntriesCommandResponse($closedByUser);
    }
}
