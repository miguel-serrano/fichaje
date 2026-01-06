<?php

namespace App\DDD\TimeTracking\Application\Service;

use App\DDD\Notification\Application\NotificationService;
use App\DDD\Notification\Domain\Channel;
use App\DDD\Notification\Domain\Notification;
use App\DDD\Notification\Domain\NotificationType;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TimeTrackingService
{
    private const MAX_HOURS = 8;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private ?NotificationService $notificationService = null
    ) {}

    public function clockIn(string $userUuid): void
    {
        $user = $this->userRepository->findByUuid(new Uuid($userUuid));

        if (! $user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        $user->clockIn();

        $this->userRepository->save($user);
    }

    public function clockOut(string $userUuid, ?int $timeEntryId = null): void
    {
        $user = $this->userRepository->findByUuid(new Uuid($userUuid));

        if (! $user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        if ($timeEntryId !== null) {
            $entryToClose = null;
            foreach ($user->timeEntries() as $entry) {
                if ($entry->id() && $entry->id()->value() === $timeEntryId) {
                    $entryToClose = $entry;
                    break;
                }
            }

            if (! $entryToClose) {
                throw new \InvalidArgumentException('Registro horario no encontrado.');
            }

            if (! $entryToClose->isOpen()) {
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

        if (! $user) {
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

        if (! $user) {
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

        $this->notifyAffectedUsers($closedByUser);

        Log::info('Fichajes huérfanos cerrados', [
            'total' => count($orphanEntries),
            'users_affected' => count($closedByUser),
        ]);

        return $closedByUser;
    }

    /**
     * @param  array<int, array<int, array<string, mixed>>>  $closedByUser
     */
    private function notifyAffectedUsers(array $closedByUser): void
    {
        if (! $this->notificationService) {
            return;
        }

        foreach ($closedByUser as $userId => $entries) {
            $user = $this->userRepository->findById(new UserId($userId));
            if ($user) {
                $notification = new Notification(
                    type: NotificationType::TimeEntryAutoClosed,
                    title: 'Fichaje cerrado automáticamente',
                    message: $this->buildNotificationMessage($entries),
                    data: ['entries' => $entries],
                    channels: [Channel::Database]
                );
                $this->notificationService->notify($user, $notification);
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     */
    private function buildNotificationMessage(array $entries): string
    {
        if (count($entries) === 1) {
            $entry = $entries[0];
            $entrada = Carbon::parse($entry['entrada']);
            $salida = Carbon::parse($entry['salida']);
            $reason = $entry['reason'] === 'max_hours_exceeded'
                ? 'al alcanzar el límite de 8 horas diarias'
                : 'al final del día';

            return sprintf(
                'Entrada: %s a las %s. Cerrado automáticamente a las %s %s.',
                $entrada->format('d/m/Y'),
                $entrada->format('H:i'),
                $salida->format('H:i'),
                $reason
            );
        }

        return sprintf('%d fichajes se cerraron automáticamente.', count($entries));
    }
}
