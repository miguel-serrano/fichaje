<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Infrastructure\Persistence\Eloquent;

use App\DDD\Shared\Domain\ValueObject\UnixTimestamp;
use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\TimeEntry as TimeEntryModel;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

class EloquentTimeEntryRepository implements TimeEntryRepositoryInterface
{
    private string $tableName;

    public function __construct(private ConnectionInterface $connection)
    {
        $this->tableName = TimeEntryModel::tableName();
    }

    public function findById(TimeEntryId $id): ?TimeEntry
    {
        $row = $this->query()->where('id', $id->value())->first();

        if (!$row) {
            return null;
        }

        return $this->toDomainEntity($row);
    }

    public function save(TimeEntry $timeEntry): TimeEntry
    {
        $entryId = $timeEntry->id()?->value();
        $now = UnixTimestamp::now()->value();

        $this->connection->transaction(function () use ($timeEntry, &$entryId, $now) {
            $data = [
                'user_id' => $timeEntry->userId()->value(),
                'entrada' => $timeEntry->startTime(),
                'salida' => $timeEntry->endTime(),
                'auto_closed' => $timeEntry->isAutoClosed(),
                'auto_close_reason' => $timeEntry->autoCloseReason(),
                'updated_at' => $now,
            ];

            if ($entryId) {
                $this->query()->where('id', $entryId)->update($data);
            } else {
                $data['created_at'] = $now;
                $entryId = $this->query()->insertGetId($data);
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

        $this->query()->where('id', $timeEntry->id()->value())->update([
            'user_id' => $timeEntry->userId()->value(),
            'entrada' => $timeEntry->startTime(),
            'salida' => $timeEntry->endTime(),
            'auto_closed' => $timeEntry->isAutoClosed(),
            'auto_close_reason' => $timeEntry->autoCloseReason(),
            'updated_at' => UnixTimestamp::now()->value(),
        ]);
    }

    /**
     * @return TimeEntry[]
     */
    public function findOrphanEntries(): array
    {
        $todayStart = strtotime('today 00:00:00');

        return $this->query()
            ->whereNull('salida')
            ->where('entrada', '<', $todayStart)
            ->get()
            ->map(fn (\stdClass $row) => $this->toDomainEntity($row))
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
        $dateString = date('Y-m-d', $dateTimestamp);
        $dayStart = strtotime($dateString.' 00:00:00');
        $dayEnd = strtotime($dateString.' 23:59:59');

        $query = $this->query()
            ->where('user_id', $userId->value())
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
        $this->query()->where('id', $id->value())->update([
            'salida' => $closedAt,
            'auto_closed' => true,
            'auto_close_reason' => $reason,
            'updated_at' => UnixTimestamp::now()->value(),
        ]);
    }

    private function query(): Builder
    {
        return $this->connection->table($this->tableName);
    }

    private function toDomainEntity(\stdClass $row): TimeEntry
    {
        return TimeEntry::fromPrimitives(
            $row->id,
            $row->user_id,
            $row->entrada,
            $row->salida,
            (bool) ($row->auto_closed ?? false),
            $row->auto_close_reason
        );
    }
}
