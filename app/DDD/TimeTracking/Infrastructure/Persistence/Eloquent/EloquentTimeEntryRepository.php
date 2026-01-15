<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Infrastructure\Persistence\Eloquent;

use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\TimeEntry;
use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\TimeEntry as TimeEntryModel;
use Carbon\Carbon;
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

        DB::transaction(function () use ($timeEntry, &$entryId) {
            $data = [
                'user_id' => $timeEntry->userId()->value(),
                'entrada' => $timeEntry->startTime()->format('Y-m-d H:i:s'),
                'salida' => $timeEntry->endTime()?->format('Y-m-d H:i:s'),
                'auto_closed' => $timeEntry->isAutoClosed(),
                'auto_close_reason' => $timeEntry->autoCloseReason(),
            ];

            if ($entryId) {
                $model = TimeEntryModel::find($entryId);
                if ($model) {
                    $model->update($data);
                }
            } else {
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
                'entrada' => $timeEntry->startTime()->format('Y-m-d H:i:s'),
                'salida' => $timeEntry->endTime()?->format('Y-m-d H:i:s'),
                'auto_closed' => $timeEntry->isAutoClosed(),
                'auto_close_reason' => $timeEntry->autoCloseReason(),
            ]);
        }
    }

    /**
     * @return TimeEntry[]
     */
    public function findOrphanEntries(): array
    {
        return TimeEntryModel::whereNull('salida')
            ->whereDate('entrada', '<', today())
            ->get()
            ->map(fn (TimeEntryModel $model) => $this->toDomainEntity($model))
            ->all();
    }

    public function getWorkedSecondsByUserAndDate(UserId $userId, Carbon $date, ?TimeEntryId $excludeEntryId = null): int
    {
        $query = TimeEntryModel::where('user_id', $userId->value())
            ->whereDate('entrada', $date->toDateString())
            ->whereNotNull('salida');

        if (null !== $excludeEntryId) {
            $query->where('id', '!=', $excludeEntryId->value());
        }

        $entries = $query->get();
        $totalSeconds = 0;

        foreach ($entries as $entry) {
            $entrada = Carbon::instance($entry->entrada);
            $salida = Carbon::instance($entry->salida);
            $totalSeconds += (int) $entrada->diffInSeconds($salida);
        }

        return $totalSeconds;
    }

    public function closeWithAutoClosed(TimeEntryId $id, Carbon $closedAt, string $reason): void
    {
        $model = TimeEntryModel::find($id->value());
        if ($model) {
            $model->update([
                'salida' => $closedAt,
                'auto_closed' => true,
                'auto_close_reason' => $reason,
            ]);
        }
    }

    private function toDomainEntity(TimeEntryModel $model): TimeEntry
    {
        return TimeEntry::fromPrimitives(
            $model->id,
            $model->user_id,
            $model->entrada->format('Y-m-d H:i:s'),
            $model->salida?->format('Y-m-d H:i:s'),
            (bool) ($model->auto_closed ?? false),
            $model->auto_close_reason
        );
    }
}
