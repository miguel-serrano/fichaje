<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Service;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
use App\DDD\TimeTracking\Domain\Exceptions\NoOpenTimeEntryException;
use App\DDD\TimeTracking\Domain\Exceptions\OpenTimeEntryAlreadyExistsException;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Services\DailyLimitValidatorService;
use App\DDD\TimeTracking\Domain\Services\OrphanTimeEntryCloserService;
use App\DDD\TimeTracking\Domain\Services\TimeEntryCalculationService;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Uuid;
use Psr\Log\LoggerInterface;

final class TimeTrackingService
{
    private function __construct(
        private UserRepositoryInterface $userRepository,
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private PermissionCheckerInterface $permissionChecker,
        private TimeEntryCalculationService $calculationService,
        private DailyLimitValidatorService $dailyLimitValidator,
        private OrphanTimeEntryCloserService $orphanCloser,
        private LoggerInterface $logger,
    ) {
    }

    public static function create(
        UserRepositoryInterface $userRepository,
        TimeEntryRepositoryInterface $timeEntryRepository,
        PermissionCheckerInterface $permissionChecker,
        LoggerInterface $logger,
    ): self {
        $calculationService = TimeEntryCalculationService::create();
        $dailyLimitValidator = DailyLimitValidatorService::create($calculationService);
        $orphanCloser = OrphanTimeEntryCloserService::create();

        return new self(
            $userRepository,
            $timeEntryRepository,
            $permissionChecker,
            $calculationService,
            $dailyLimitValidator,
            $orphanCloser,
            $logger
        );
    }

    public function clockIn(Uuid $userUuid): void
    {
        $user = $this->userRepository->findByUuid($userUuid);

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        if ($this->calculationService->hasOpenEntry($user->timeEntries())) {
            throw new OpenTimeEntryAlreadyExistsException();
        }

        if (!$this->permissionChecker->isSuperAdmin($user)) {
            $this->dailyLimitValidator->ensureDailyLimitNotExceeded($user->timeEntries());
        }

        $timeEntry = TimeEntry::create($user->id());

        $this->timeEntryRepository->save($timeEntry);
    }

    public function clockOut(string $userUuid): void
    {
        $user = $this->userRepository->findByUuid(new Uuid($userUuid));

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        $openEntry = $this->calculationService->findOpenEntry($user->timeEntries());

        if (!$openEntry) {
            throw new NoOpenTimeEntryException();
        }

        $openEntry->close();

        $this->timeEntryRepository->update($openEntry);
    }

    public function getAccumulatedSeconds(string $userUuid): int
    {
        $user = $this->userRepository->findByUuid(new Uuid($userUuid));

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        return $this->calculationService->calculateTodayAccumulatedSeconds($user->timeEntries());
    }

    public function hasOpenTimeEntry(string $userUuid): bool
    {
        $user = $this->userRepository->findByUuid(new Uuid($userUuid));

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        return $this->calculationService->hasOpenEntry($user->timeEntries());
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
