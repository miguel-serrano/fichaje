<?php

namespace App\Console\Commands;

use App\DDD\Notification\Application\NotificationService;
use App\DDD\Notification\Domain\Entity\Notification;
use App\DDD\Notification\Domain\ValueObjects\Channel;
use App\DDD\Notification\Domain\ValueObjects\NotificationType;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Uuid;
use App\Models\TimeEntry as TimeEntryModel;
use App\Models\User as UserModel;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;

class CloseUserOrphanTimeEntryCommand extends Command
{
    private const MAX_HOURS = 8;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'time-entries:close-user {uuid : UUID del usuario}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cierra fichajes huérfanos de un usuario específico';

    private ConnectionInterface $connection;

    /**
     * Execute the console command.
     */
    public function handle(
        UserRepositoryInterface $userRepository,
        NotificationService $notificationService,
        ConnectionInterface $connection,
    ): int {
        $this->connection = $connection;
        $uuid = $this->argument('uuid');

        $this->info("Buscando fichajes huérfanos para usuario: {$uuid}");

        $user = $userRepository->findByUuid(new Uuid($uuid));

        if (!$user) {
            $this->error("Usuario no encontrado con UUID: {$uuid}");

            return Command::FAILURE;
        }

        $this->info("Usuario encontrado: {$user->name()->value()} ({$user->email()->value()})");

        $timeEntriesTable = TimeEntryModel::tableName();
        $usersTable = UserModel::tableName();

        $orphanEntries = $this->connection->table($timeEntriesTable)
            ->join($usersTable, "{$timeEntriesTable}.user_id", '=', "{$usersTable}.id")
            ->where("{$usersTable}.uuid", $uuid)
            ->whereNull("{$timeEntriesTable}.salida")
            ->select("{$timeEntriesTable}.*")
            ->get();

        if ($orphanEntries->isEmpty()) {
            $this->info('No se encontraron fichajes huérfanos para este usuario.');

            return Command::SUCCESS;
        }

        $closedEntries = [];
        $maxSecondsDaily = self::MAX_HOURS * 3600;

        foreach ($orphanEntries as $entry) {
            $entrada = $entry->entrada;
            $entradaDate = date('Y-m-d', $entrada);
            $endOfDay = strtotime($entradaDate.' 23:59:59');

            // Calcular segundos ya trabajados ese día (otros fichajes cerrados)
            $workedSecondsToday = $this->getWorkedSecondsByUserAndDate(
                $entry->user_id,
                $entrada,
                $entry->id
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

            $this->timeEntriesQuery()
                ->where('id', $entry->id)
                ->update([
                    'salida' => $salida,
                    'auto_closed' => true,
                    'auto_close_reason' => $reason,
                    'updated_at' => time(),
                ]);

            $closedEntries[] = [
                'entry_id' => $entry->id,
                'entrada' => date('Y-m-d H:i:s', $entrada),
                'salida' => date('Y-m-d H:i:s', $salida),
                'reason' => $reason,
            ];

            $workedHours = gmdate('H:i:s', $workedSecondsToday);
            $this->line("  - Fichaje #{$entry->id}: ".date('d/m/Y H:i', $entrada).' → cerrado a '.date('H:i:s', $salida)." (ya trabajado: {$workedHours})");
        }

        // Notificar al usuario
        $notification = Notification::create(
            type: NotificationType::TimeEntryAutoClosed,
            title: 'Fichaje cerrado automáticamente',
            message: $this->buildNotificationMessage($closedEntries),
            data: ['entries' => $closedEntries],
            channels: [Channel::Database]
        );
        $notificationService->notify($user, $notification);

        Log::info('Fichajes cerrados manualmente para usuario', [
            'user_uuid' => $uuid,
            'entries_closed' => count($closedEntries),
        ]);

        $this->info(sprintf('Cerrados %d fichajes huérfanos.', count($closedEntries)));

        return Command::SUCCESS;
    }

    private function getWorkedSecondsByUserAndDate(int $userId, int $dateTimestamp, ?int $excludeEntryId = null): int
    {
        $dateString = date('Y-m-d', $dateTimestamp);
        $dayStart = strtotime($dateString.' 00:00:00');
        $dayEnd = strtotime($dateString.' 23:59:59');

        $query = $this->timeEntriesQuery()
            ->where('user_id', $userId)
            ->where('entrada', '>=', $dayStart)
            ->where('entrada', '<=', $dayEnd)
            ->whereNotNull('salida');

        if (null !== $excludeEntryId) {
            $query->where('id', '!=', $excludeEntryId);
        }

        $entries = $query->get();
        $totalSeconds = 0;

        foreach ($entries as $entry) {
            $totalSeconds += $entry->salida - $entry->entrada;
        }

        return $totalSeconds;
    }

    private function timeEntriesQuery(): Builder
    {
        return $this->connection->table(TimeEntryModel::tableName());
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     */
    private function buildNotificationMessage(array $entries): string
    {
        if (1 === count($entries)) {
            $entry = $entries[0];
            $entradaTimestamp = strtotime($entry['entrada']);
            $salidaTimestamp = strtotime($entry['salida']);
            $reason = 'max_hours_exceeded' === $entry['reason']
                ? 'al alcanzar el límite de 8 horas diarias'
                : 'al final del día';

            return sprintf(
                'Entrada: %s a las %s. Cerrado automáticamente a las %s %s.',
                date('d/m/Y', $entradaTimestamp),
                date('H:i', $entradaTimestamp),
                date('H:i', $salidaTimestamp),
                $reason
            );
        }

        return sprintf('%d fichajes se cerraron automáticamente.', count($entries));
    }
}
