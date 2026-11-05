<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Service;

use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
use App\DDD\TimeTracking\Domain\Exceptions\NoOpenTimeEntryException;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Services\OrphanTimeEntryCloserService;
use App\DDD\TimeTracking\Domain\Services\TimeEntryCalculationService;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Uuid;
use Psr\Log\LoggerInterface;

final class TimeTrackingService
{
    private function __construct(
        private UserRepositoryInterface $userRepository,
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private TimeEntryCalculationService $calculationService,
        private OrphanTimeEntryCloserService $orphanCloser,
        private LoggerInterface $logger,
    ) {
    }

    public static function create(
        UserRepositoryInterface $userRepository,
        TimeEntryRepositoryInterface $timeEntryRepository,
        LoggerInterface $logger,
    ): self {
        return new self(
            $userRepository,
            $timeEntryRepository,
            TimeEntryCalculationService::create(),
            OrphanTimeEntryCloserService::create(),
            $logger
        );
    }

    /**
     * Obtiene la entrada abierta del usuario.
     * El usuario debe venir con su ventana de fichaje cargada (ActiveUserRepositoryInterface).
     *
     * @throws NoOpenTimeEntryException si no tiene una entrada abierta
     */
    public function getOpenEntry(User $user): TimeEntry
    {
        $openEntry = $this->calculationService->findOpenEntry($user->timeEntries());

        if (!$openEntry) {
            throw new NoOpenTimeEntryException();
        }

        return $openEntry;
    }

    public function getAccumulatedSeconds(string $userUuid): int
    {
        $user = $this->userRepository->findByUuid(new Uuid($userUuid));

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        return $this->calculationService->calculateTodayAccumulatedSeconds(
            $this->timeEntryRepository->findClockingWindowByUserId($user->id())
        );
    }

    public function hasOpenTimeEntry(string $userUuid): bool
    {
        $user = $this->userRepository->findByUuid(new Uuid($userUuid));

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        return $this->calculationService->hasOpenEntry(
            $this->timeEntryRepository->findClockingWindowByUserId($user->id())
        );
    }

    /**
     * Cierra automáticamente los fichajes huérfanos de días anteriores.
     * Retorna los datos de los fichajes cerrados para que el handler pueda notificar.
     *
     * @return array<int, array<int, array<string, mixed>>> Registros cerrados agrupados por user_id
     */
    public function closeOrphanTimeEntries(): array
    {
        $orphanEntries = $this->timeEntryRepository->findOrphanEntries();

        $closedByUser = [];

        foreach ($orphanEntries as $entry) {
            $workedSecondsToday = $this->timeEntryRepository->getWorkedSecondsByUserAndDate(
                $entry->userId(),
                $entry->startTime(),
                $entry->id()
            );

            $closureResult = $this->orphanCloser->calculateClosure($entry, $workedSecondsToday);

            $this->timeEntryRepository->closeWithAutoClosed(
                $closureResult->entryId(),
                $closureResult->closeTime(),
                $closureResult->reason()
            );

            $closedByUser[$entry->userId()->value()][] = $closureResult->toArray();
        }

        $this->logger->info('Fichajes huérfanos cerrados', [
            'total' => count($orphanEntries),
            'users_affected' => count($closedByUser),
        ]);

        return $closedByUser;
    }
}
