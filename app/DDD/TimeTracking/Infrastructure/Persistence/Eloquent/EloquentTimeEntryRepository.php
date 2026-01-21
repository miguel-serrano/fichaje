<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Infrastructure\Persistence\Eloquent;

use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\TimeEntry as TimeEntryModel;
use Illuminate\Support\Facades\DB;

class EloquentTimeEntryRepository implements TimeEntryRepositoryInterface
{
    public function findById(TimeEntryId $id): ?TimeEntry
    {
        $model = TimeEntryModel::find($id->value());

        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function save(TimeEntry $timeEntry): TimeEntry
    {
        $entryId = $timeEntry->id()?->value();
        $now = time();

        DB::transaction(function () use ($timeEntry, &$entryId, $now) {
            $data = [
                'user_id' => $timeEntry->userId()->value(),
                'entrada' => $timeEntry->startTime(),
                'salida' => $timeEntry->endTime(),
                'auto_closed' => $timeEntry->isAutoClosed(),
                'auto_close_reason' => $timeEntry->autoCloseReason(),
                'updated_at' => $now,
            ];

            if ($entryId) {
                $model = TimeEntryModel::find($entryId);
                if ($model) {
                    $model->update($data);
                }
            } else {
                $data['created_at'] = $now;
                $model = TimeEntryModel::create($data);
                $entryId = $model->id;
            }
        });

        $timeEntry->setId(new TimeEntryId($entryId));

        return $timeEntry;
    }

    public function update(TimeEntry $timeEntry): void
    {
        if (!$timeEntry->id()) {
            throw new \InvalidArgumentException('Cannot update a TimeEntry without an ID');
        }

        $model = TimeEntryModel::find($timeEntry->id()->value());
        if ($model) {
            $model->update([
                'user_id' => $timeEntry->userId()->value(),
                'entrada' => $timeEntry->startTime(),
                'salida' => $timeEntry->endTime(),
                'auto_closed' => $timeEntry->isAutoClosed(),
                'auto_close_reason' => $timeEntry->autoCloseReason(),
                'updated_at' => time(),
            ]);
        }
    }

    /**
     * @return TimeEntry[]
     */
    public function findOrphanEntries(): array
    {
        // Timestamp de inicio del día actual (medianoche)
        $todayStart = strtotime('today 00:00:00');

        return TimeEntryModel::whereNull('salida')
            ->where('entrada', '<', $todayStart)
            ->get()
            ->map(fn (TimeEntryModel $model) => $this->toDomainEntity($model))
            ->all();
    }

    /**
     * Obtiene los segundos trabajados por un usuario en una fecha específica.
     *
     * @param UserId           $userId         ID del usuario
     * @param int              $dateTimestamp  Timestamp Unix de la fecha (cualquier momento del día)
     * @param TimeEntryId|null $excludeEntryId ID de entrada a excluir (opcional)
     */
    public function getWorkedSecondsByUserAndDate(UserId $userId, int $dateTimestamp, ?TimeEntryId $excludeEntryId = null): int
    {
        // Calcular inicio y fin del día desde el timestamp
        $dateString = date('Y-m-d', $dateTimestamp);
        $dayStart = strtotime($dateString.' 00:00:00');
        $dayEnd = strtotime($dateString.' 23:59:59');

        $query = TimeEntryModel::where('user_id', $userId->value())
            ->where('entrada', '>=', $dayStart)
            ->where('entrada', '<=', $dayEnd)
            ->whereNotNull('salida');

        if (null !== $excludeEntryId) {
            $query->where('id', '!=', $excludeEntryId->value());
        }

        $entries = $query->get();
        $totalSeconds = 0;

        foreach ($entries as $entry) {
            $totalSeconds += $entry->salida - $entry->entrada;
        }

        return $totalSeconds;
    }

    /**
     * Cierra una entrada de tiempo con auto-cierre.
     *
     * @param TimeEntryId $id       ID de la entrada
     * @param int         $closedAt Timestamp Unix del momento de cierre
     * @param string      $reason   Razón del auto-cierre
     */
    public function closeWithAutoClosed(TimeEntryId $id, int $closedAt, string $reason): void
    {
        $model = TimeEntryModel::find($id->value());
        if ($model) {
            $model->update([
                'salida' => $closedAt,
                'auto_closed' => true,
                'auto_close_reason' => $reason,
                'updated_at' => time(),
            ]);
        }
    }

    private function toDomainEntity(TimeEntryModel $model): TimeEntry
    {
        return TimeEntry::fromPrimitives(
            $model->id,
            $model->user_id,
            $model->entrada,
            $model->salida,
            (bool) ($model->auto_closed ?? false),
            $model->auto_close_reason
        );
    }
}
