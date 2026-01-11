<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Infrastructure\Persistence\Eloquent;

use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Holiday\Domain\Exceptions\HolidayRequestNotFoundException;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\ValueObjects\DateRange;
use App\DDD\Holiday\Domain\ValueObjects\HolidayRequestId;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\HolidayRequest as HolidayRequestModel;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

class EloquentHolidayRepository implements HolidayRepositoryInterface
{
    private string $holidayRequestTable;

    public function __construct(
        private ConnectionInterface $connection,
    ) {
        $this->holidayRequestTable = HolidayRequestModel::tableName();
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
            $this->query()
                ->where('id', $request->id()->value())
                ->update($data);

            return $this->findByIdOrFail($request->id());
        }

        $data['created_at'] = now();
        $id = $this->query()->insertGetId($data);
        $request->setId(new HolidayRequestId($id));

        return $this->findByIdOrFail(new HolidayRequestId($id));
    }

    public function findById(HolidayRequestId $id): ?HolidayRequest
    {
        $row = $this->query()
            ->where('id', $id->value())
            ->first();

        if (!$row) {
            return null;
        }

        return $this->mapToEntity($row);
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
        $rows = $this->query()
            ->where('user_id', $userId->value())
            ->orderBy('created_at', 'desc')
            ->get();

        return $rows->map(fn (\stdClass $row) => $this->mapToEntity($row))->toArray();
    }

    /**
     * @return HolidayRequest[]
     */
    public function findPending(): array
    {
        $rows = $this->query()
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return $rows->map(fn (\stdClass $row) => $this->mapToEntity($row))->toArray();
    }

    /**
     * @return HolidayRequest[]
     */
    public function findApproved(): array
    {
        $rows = $this->query()
            ->where('status', 'approved')
            ->orderBy('start_date', 'asc')
            ->get();

        return $rows->map(fn (\stdClass $row) => $this->mapToEntity($row))->toArray();
    }

    /**
     * @return HolidayRequest[]
     */
    public function findAll(): array
    {
        $rows = $this->query()
            ->orderBy('created_at', 'desc')
            ->get();

        return $rows->map(fn (\stdClass $row) => $this->mapToEntity($row))->toArray();
    }

    public function hasOverlapping(UserId $userId, DateRange $range, ?HolidayRequestId $excludeId = null): bool
    {
        $query = $this->query()
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
        return $this->query()
            ->where('id', $id->value())
            ->delete() > 0;
    }

    private function query(): Builder
    {
        return $this->connection->table($this->holidayRequestTable);
    }

    private function mapToEntity(\stdClass $row): HolidayRequest
    {
        return HolidayRequest::fromPrimitives([
            'id' => $row->id,
            'user_id' => $row->user_id,
            'start_date' => $row->start_date,
            'end_date' => $row->end_date,
            'status' => $row->status,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);
    }
}
