<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Infrastructure\Persistence\Eloquent;

use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Holiday\Domain\Exceptions\HolidayRequestNotFoundException;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\ValueObjects\DateRange;
use App\DDD\Holiday\Domain\ValueObjects\HolidayRequestId;
use App\DDD\User\Domain\ValueObjects\UserId;
use Illuminate\Support\Facades\DB;

class EloquentHolidayRepository implements HolidayRepositoryInterface
{
    private function getTable(): string
    {
        return 'holiday_requests';
    }

    public function save(HolidayRequest $request): HolidayRequest
    {
        $data = [
            'user_id' => $request->userId()->value(),
            'start_date' => $request->dateRange()->startDateFormatted(),
            'end_date' => $request->dateRange()->endDateFormatted(),
            'status' => $request->status()->value,
            'updated_at' => now(),
        ];

        if ($request->id()) {
            DB::table($this->getTable())
                ->where('id', $request->id()->value())
                ->update($data);

            return $this->findByIdOrFail($request->id());
        }

        $data['created_at'] = now();
        $id = DB::table($this->getTable())->insertGetId($data);
        $request->setId(new HolidayRequestId($id));

        return $this->findByIdOrFail(new HolidayRequestId($id));
    }

    public function findById(HolidayRequestId $id): ?HolidayRequest
    {
        $record = DB::table($this->getTable())
            ->where('id', $id->value())
            ->first();

        if (!$record) {
            return null;
        }

        return $this->mapToEntity($record);
    }

    public function findByIdOrFail(HolidayRequestId $id): HolidayRequest
    {
        $request = $this->findById($id);

        if (!$request) {
            throw HolidayRequestNotFoundException::withId($id->value());
        }

        return $request;
    }

    /**
     * @return HolidayRequest[]
     */
    public function findByUserId(UserId $userId): array
    {
        $records = DB::table($this->getTable())
            ->where('user_id', $userId->value())
            ->orderBy('created_at', 'desc')
            ->get();

        return $records->map(fn ($record) => $this->mapToEntity($record))->toArray();
    }

    /**
     * @return HolidayRequest[]
     */
    public function findPending(): array
    {
        $records = DB::table($this->getTable())
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return $records->map(fn ($record) => $this->mapToEntity($record))->toArray();
    }

    /**
     * @return HolidayRequest[]
     */
    public function findApproved(): array
    {
        $records = DB::table($this->getTable())
            ->where('status', 'approved')
            ->orderBy('start_date', 'asc')
            ->get();

        return $records->map(fn ($record) => $this->mapToEntity($record))->toArray();
    }

    /**
     * @return HolidayRequest[]
     */
    public function findAll(): array
    {
        $records = DB::table($this->getTable())
            ->orderBy('created_at', 'desc')
            ->get();

        return $records->map(fn ($record) => $this->mapToEntity($record))->toArray();
    }

    public function hasOverlapping(UserId $userId, DateRange $range, ?HolidayRequestId $excludeId = null): bool
    {
        $query = DB::table($this->getTable())
            ->where('user_id', $userId->value())
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($range) {
                $q->where(function ($inner) use ($range) {
                    $inner->where('start_date', '<=', $range->endDateFormatted())
                        ->where('end_date', '>=', $range->startDateFormatted());
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId->value());
        }

        return $query->exists();
    }

    public function delete(HolidayRequestId $id): bool
    {
        return DB::table($this->getTable())
            ->where('id', $id->value())
            ->delete() > 0;
    }

    private function mapToEntity(object $record): HolidayRequest
    {
        return HolidayRequest::fromPrimitives([
            'id' => $record->id,
            'user_id' => $record->user_id,
            'start_date' => $record->start_date,
            'end_date' => $record->end_date,
            'status' => $record->status,
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
        ]);
    }
}
