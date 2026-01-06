<?php

namespace App\DDD\TimeTracking\Services;

use App\DDD\Notification\Application\NotificationService;
use App\DDD\Notification\Domain\Channel;
use App\DDD\Notification\Domain\Notification;
use App\DDD\Notification\Domain\NotificationType;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimeTrackingService
{
    private const MAX_HOURS = 8;

    public function __construct(
        protected UserRepositoryInterface $repository,
        protected ?NotificationService $notificationService = null
    ) {}

    public function clockIn(string $userUuid): void
    {
        $user = $this->repository->findByUuid(new Uuid($userUuid));

        if (! $user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        $user->ficharEntrada();

        $this->repository->save($user);
    }

    public function clockOut(string $userUuid, ?int $timeEntryId = null): void
    {
        $user = $this->repository->findByUuid(new Uuid($userUuid));

        if (! $user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        // Si se proporciona un ID específico, cerrar ese registro
        if ($timeEntryId !== null) {
            $registroToClose = null;
            foreach ($user->registrosHorarios() as $registro) {
                if ($registro->id() && $registro->id()->getValue() === $timeEntryId) {
                    $registroToClose = $registro;
                    break;
                }
            }

            if (! $registroToClose) {
                throw new \InvalidArgumentException('Registro horario no encontrado.');
            }

            if (! $registroToClose->isAbierto()) {
                throw new \InvalidArgumentException('El registro horario ya está cerrado.');
            }

            $registroToClose->cerrar();
        } else {
            // Comportamiento original: cerrar el registro abierto actual
            $user->ficharSalida();
        }

        $this->repository->save($user);
    }

    public function getAccumulatedSeconds(string $userUuid): int
    {
        $user = $this->repository->findByUuid(new Uuid($userUuid));

        if (! $user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        $today = Carbon::now()->startOfDay();
        $suma = 0;

        foreach ($user->registrosHorarios() as $registro) {
            if (Carbon::instance($registro->entrada())->isSameDay($today)) {
                $suma += $registro->segundosTrabajados();
            }
        }

        return $suma;
    }

    public function hasOpenTimeEntry(string $userUuid): bool
    {
        $user = $this->repository->findByUuid(new Uuid($userUuid));

        if (! $user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        foreach ($user->registrosHorarios() as $registro) {
            if ($registro->isAbierto()) {
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
        $orphanEntries = DB::table('time_entries')
            ->whereNull('salida')
            ->whereDate('entrada', '<', today())
            ->get();

        $closedByUser = [];

        foreach ($orphanEntries as $entry) {
            $entrada = Carbon::parse($entry->entrada);
            $endOfDay = $entrada->copy()->endOfDay();
            $maxSecondsDaily = self::MAX_HOURS * 3600;

            // Calcular segundos ya trabajados ese día (otros fichajes cerrados)
            $workedSecondsToday = $this->getWorkedSecondsByUserAndDate(
                $entry->user_id,
                $entrada->toDateString(),
                $entry->id
            );

            $remainingSeconds = $maxSecondsDaily - $workedSecondsToday;

            // Determinar hora de cierre y motivo
            if ($remainingSeconds <= 0) {
                // Ya alcanzó las 8 horas, cerrar en la entrada
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

            DB::table('time_entries')
                ->where('id', $entry->id)
                ->update([
                    'salida' => $salida,
                    'auto_closed' => true,
                    'auto_close_reason' => $reason,
                    'updated_at' => now(),
                ]);

            $closedByUser[$entry->user_id][] = [
                'entry_id' => $entry->id,
                'entrada' => $entrada->toDateTimeString(),
                'salida' => $salida->toDateTimeString(),
                'reason' => $reason,
            ];
        }

        // Notificar a cada usuario afectado
        if ($this->notificationService) {
            foreach ($closedByUser as $userId => $entries) {
                $user = $this->repository->findById(new UserId($userId));
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

        Log::info('Fichajes huérfanos cerrados', [
            'total' => $orphanEntries->count(),
            'users_affected' => count($closedByUser),
        ]);

        return $closedByUser;
    }

    /**
     * Calcula los segundos trabajados por un usuario en una fecha específica.
     * Excluye opcionalmente un entry_id (el huérfano que estamos procesando).
     */
    private function getWorkedSecondsByUserAndDate(int $userId, string $date, ?int $excludeEntryId = null): int
    {
        $query = DB::table('time_entries')
            ->where('user_id', $userId)
            ->whereDate('entrada', $date)
            ->whereNotNull('salida');

        if ($excludeEntryId !== null) {
            $query->where('id', '!=', $excludeEntryId);
        }

        $entries = $query->get();
        $totalSeconds = 0;

        foreach ($entries as $entry) {
            $entrada = Carbon::parse($entry->entrada);
            $salida = Carbon::parse($entry->salida);
            $totalSeconds += $entrada->diffInSeconds($salida);
        }

        return $totalSeconds;
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
