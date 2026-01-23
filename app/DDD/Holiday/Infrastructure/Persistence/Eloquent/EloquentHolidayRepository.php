<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Infrastructure\Persistence\Eloquent;

use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Holiday\Domain\Exceptions\HolidayRequestNotFoundException;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\ValueObjects\DateRange;
use App\DDD\Holiday\Domain\ValueObjects\HolidayRequestId;
use App\DDD\Shared\Domain\ValueObject\UnixTimestamp;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\HolidayRequest as HolidayRequestModel;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

class EloquentHolidayRepository implements HolidayRepositoryInterface
{
    private string $tableName;

    public function __construct(private ConnectionInterface $connection)
    {
        $this->tableName = HolidayRequestModel::tableName();
    }

    public function save(HolidayRequest $request): HolidayRequest
    {
        $requestId = $request->id()?->value();
        $now = UnixTimestamp::now()->value();

        $this->connection->transaction(function () use ($request, &$requestId, $now) {
            $data = [
                'user_id' => $request->userId()->value(),
                'start_date' => $request->dateRange()->startDate(),
                'end_date' => $request->dateRange()->endDate(),
                'status' => $request->status()->value,
                'updated_at' => $now,
            ];

            if ($requestId) {
                $exists = $this->query()->where('id', $requestId)->exists();
                if (!$exists) {
                    throw HolidayRequestNotFoundException::withId($requestId);
                }
                $this->query()->where('id', $requestId)->update($data);
            } else {
                $data['created_at'] = $now;
                $requestId = $this->query()->insertGetId($data);
            }
        });

        return $this->findByIdOrFail(HolidayRequestId::make($requestId));
    }

    public function findById(HolidayRequestId $id): ?HolidayRequest
    {
        $row = $this->query()->where('id', $id->value())->first();

        if (!$row) {
            return null;
        }

        return $this->toDomainEntity($row);
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
        return $this->query()
            ->where('user_id', $userId->value())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (\stdClass $row) => $this->toDomainEntity($row))
            ->toArray();
    }

    /**
     * @return HolidayRequest[]
     */
    public function findPending(): array
    {
        return $this->query()
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn (\stdClass $row) => $this->toDomainEntity($row))
            ->toArray();
    }

    /**
     * @return HolidayRequest[]
     */
    public function findApproved(): array
    {
        return $this->query()
            ->where('status', 'approved')
            ->orderBy('start_date', 'asc')
            ->get()
            ->map(fn (\stdClass $row) => $this->toDomainEntity($row))
            ->toArray();
    }

    /**
     * @return HolidayRequest[]
     */
    public function findAll(): array
    {
        return $this->query()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (\stdClass $row) => $this->toDomainEntity($row))
            ->toArray();
    }

    public function hasOverlapping(UserId $userId, DateRange $range, ?HolidayRequestId $excludeId = null): bool
    {
        $query = $this->query()
            ->where('user_id', $userId->value())
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($range) {
                $q->where('start_date', '<=', $range->endDate())
                    ->where('end_date', '>=', $range->startDate());
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId->value());
        }

        return $query->exists();
    }

    public function delete(HolidayRequestId $id): bool
    {
        return $this->query()->where('id', $id->value())->delete() > 0;
    }

    private function query(): Builder
    {
        return $this->connection->table($this->tableName);
    }

    private function toDomainEntity(\stdClass $row): HolidayRequest
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
