<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Service;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\TimeTracking\Domain\Exceptions\DailyTimeEntryLimitExceededException;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Uuid;
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

    public function clockIn(Uuid $userUuid): void
    {
        $user = $this->userRepository->findByUuid($userUuid);

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

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

        $todayDate = date('Y-m-d');
        $suma = 0;

        foreach ($user->timeEntries() as $entry) {
            if (date('Y-m-d', $entry->startTime()) === $todayDate) {
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
            $entrada = $entry->startTime();
            $entradaDate = date('Y-m-d', $entrada);
            $endOfDay = strtotime($entradaDate.' 23:59:59');
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
                $maxHoursLimit = $entrada + $remainingSeconds;

                if ($maxHoursLimit < $endOfDay) {
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
                'entrada' => date('Y-m-d H:i:s', $entrada),
                'salida' => date('Y-m-d H:i:s', $salida),
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
        $todayDate = date('Y-m-d');
        $todayEntries = 0;

        foreach ($user->timeEntries() as $entry) {
            if (date('Y-m-d', $entry->startTime()) === $todayDate) {
                ++$todayEntries;
            }
        }

        if ($todayEntries >= DailyTimeEntryLimitExceededException::MAX_DAILY_ENTRIES) {
            throw DailyTimeEntryLimitExceededException::withCount($todayEntries);
        }
    }
}
