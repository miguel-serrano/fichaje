<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Service;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Domain\Exceptions\DailyTimeEntryLimitExceededException;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Uuid;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

final class TimeTrackingService
{
    private const MAX_HOURS = 8;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function clockIn(string $userUuid): void
    {
        $user = $this->userRepository->findByUuid(new Uuid($userUuid));

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        // Verificar límite diario (solo para usuarios no super_admin)
        if (!$this->permissionChecker->isSuperAdmin($user)) {
            $this->ensureDailyLimitNotExceeded($user);
        }

        $user->clockIn();

        $this->userRepository->save($user);
    }

    public function clockOut(string $userUuid, ?int $timeEntryId = null): void
    {
        $user = $this->userRepository->findByUuid(new Uuid($userUuid));

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        if (null !== $timeEntryId) {
            $entryToClose = null;
            foreach ($user->timeEntries() as $entry) {
                if ($entry->id() && $entry->id()->value() === $timeEntryId) {
                    $entryToClose = $entry;
                    break;
                }
            }

            if (!$entryToClose) {
                throw new \InvalidArgumentException('Registro horario no encontrado.');
            }

            if (!$entryToClose->isOpen()) {
                throw new \InvalidArgumentException('El registro horario ya está cerrado.');
            }

            $entryToClose->close();
        } else {
            $user->clockOut();
        }

        $this->userRepository->save($user);
    }

    public function getAccumulatedSeconds(string $userUuid): int
    {
        $user = $this->userRepository->findByUuid(new Uuid($userUuid));

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        $today = Carbon::now()->startOfDay();
        $suma = 0;

        foreach ($user->timeEntries() as $entry) {
            if (Carbon::instance($entry->startTime())->isSameDay($today)) {
                $suma += $entry->workedSeconds();
            }
        }

        return $suma;
    }

    public function hasOpenTimeEntry(string $userUuid): bool
    {
        $user = $this->userRepository->findByUuid(new Uuid($userUuid));

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        foreach ($user->timeEntries() as $entry) {
            if ($entry->isOpen()) {
                return true;
            }
        }

        return false;
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
            $entrada = Carbon::instance($entry->startTime());
            $endOfDay = $entrada->copy()->endOfDay();
            $maxSecondsDaily = self::MAX_HOURS * 3600;

            $workedSecondsToday = $this->timeEntryRepository->getWorkedSecondsByUserAndDate(
                $entry->userId(),
                $entrada,
                $entry->id()
            );

            $remainingSeconds = $maxSecondsDaily - $workedSecondsToday;

            if ($remainingSeconds <= 0) {
                $salida = $entrada;
                $reason = 'max_hours_exceeded';
            } else {
                $maxHoursLimit = $entrada->copy()->addSeconds($remainingSeconds);

                if ($maxHoursLimit->lt($endOfDay)) {
                    $salida = $maxHoursLimit;
                    $reason = 'max_hours_exceeded';
                } else {
                    $salida = $endOfDay;
                    $reason = 'end_of_day';
                }
            }

            $this->timeEntryRepository->closeWithAutoClosed(
                $entry->id(),
                $salida,
                $reason
            );

            $closedByUser[$entry->userId()->value()][] = [
                'entry_id' => $entry->id()->value(),
                'entrada' => $entrada->toDateTimeString(),
                'salida' => $salida->toDateTimeString(),
                'reason' => $reason,
            ];
        }

        Log::info('Fichajes huérfanos cerrados', [
            'total' => count($orphanEntries),
            'users_affected' => count($closedByUser),
        ]);

        return $closedByUser;
    }

    private function ensureDailyLimitNotExceeded(User $user): void
    {
        $today = Carbon::today();
        $todayEntries = 0;

        foreach ($user->timeEntries() as $entry) {
            $startTimeCarbon = Carbon::instance($entry->startTime());
            if ($startTimeCarbon->isSameDay($today)) {
                ++$todayEntries;
            }
        }

        if ($todayEntries >= DailyTimeEntryLimitExceededException::MAX_DAILY_ENTRIES) {
            throw DailyTimeEntryLimitExceededException::withCount($todayEntries);
        }
    }
}
