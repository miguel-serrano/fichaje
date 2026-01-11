<?php

namespace App\DDD\TimeTracking\Infrastructure\Persistence\Eloquent;

use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\TimeEntry;
use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\TimeEntry as TimeEntryModel;
use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

class EloquentTimeEntryRepository implements TimeEntryRepositoryInterface
{
    private string $timeEntryTable;

    public function __construct(
        private ConnectionInterface $connection,
    ) {
        $this->timeEntryTable = TimeEntryModel::tableName();
    }

    public function findById(TimeEntryId $id): ?TimeEntry
    {
        $row = $this->query()->where('id', $id->value())->first();

        if (!$row) {
            return null;
        }

        return $this->toEntity($row);
    }

    public function save(TimeEntry $timeEntry): TimeEntry
    {
        $data = [
            'user_id' => $timeEntry->userId()->value(),
            'entrada' => $timeEntry->startTime()->format('Y-m-d H:i:s'),
            'salida' => $timeEntry->endTime()?->format('Y-m-d H:i:s'),
            'auto_closed' => $timeEntry->isAutoClosed(),
            'auto_close_reason' => $timeEntry->autoCloseReason(),
            'updated_at' => now(),
        ];

        if ($timeEntry->id()) {
            $this->query()
                ->where('id', $timeEntry->id()->value())
                ->update($data);

            return $timeEntry;
        }

        $data['created_at'] = now();
        $id = $this->query()->insertGetId($data);
        $timeEntry->setId(new TimeEntryId($id));

        return $timeEntry;
    }

    public function update(TimeEntry $timeEntry): void
    {
        if (!$timeEntry->id()) {
            throw new \InvalidArgumentException('Cannot update a TimeEntry without an ID');
        }

        $this->query()
            ->where('id', $timeEntry->id()->value())
            ->update([
                'user_id' => $timeEntry->userId()->value(),
                'entrada' => $timeEntry->startTime()->format('Y-m-d H:i:s'),
                'salida' => $timeEntry->endTime()?->format('Y-m-d H:i:s'),
                'auto_closed' => $timeEntry->isAutoClosed(),
                'auto_close_reason' => $timeEntry->autoCloseReason(),
                'updated_at' => now(),
            ]);
    }

    /** @return TimeEntry[] */
    public function findOrphanEntries(): array
    {
        $rows = $this->query()
            ->whereNull('salida')
            ->whereDate('entrada', '<', today())
            ->get();

        return $rows->map(fn (\stdClass $row) => $this->toEntity($row))->all();
    }

    public function getWorkedSecondsByUserAndDate(UserId $userId, Carbon $date, ?TimeEntryId $excludeEntryId = null): int
    {
        $query = $this->query()
            ->where('user_id', $userId->value())
            ->whereDate('entrada', $date->toDateString())
            ->whereNotNull('salida');

        if (null !== $excludeEntryId) {
            $query->where('id', '!=', $excludeEntryId->value());
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

    public function closeWithAutoClosed(TimeEntryId $id, Carbon $closedAt, string $reason): void
    {
        $this->query()
            ->where('id', $id->value())
            ->update([
                'salida' => $closedAt,
                'auto_closed' => true,
                'auto_close_reason' => $reason,
                'updated_at' => now(),
            ]);
    }

    private function query(): Builder
    {
        return $this->connection->table($this->timeEntryTable);
    }

    private function toEntity(\stdClass $row): TimeEntry
    {
        return TimeEntry::fromPrimitives(
            $row->id,
            $row->user_id,
            $row->entrada,
            $row->salida,
            (bool) ($row->auto_closed ?? false),
            $row->auto_close_reason ?? null
        );
    }
}
