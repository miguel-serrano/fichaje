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
use Illuminate\Support\Facades\DB;

class EloquentHolidayRepository implements HolidayRepositoryInterface
{
    public function save(HolidayRequest $request): HolidayRequest
    {
        $requestId = $request->id()?->value();

        DB::transaction(function () use ($request, &$requestId) {
            $data = [
                'user_id' => $request->userId()->value(),
                'start_date' => $request->dateRange()->startDateFormatted(),
                'end_date' => $request->dateRange()->endDateFormatted(),
                'status' => $request->status()->value,
            ];

            if ($requestId) {
                $model = HolidayRequestModel::find($requestId);
                if (!$model) {
                    throw HolidayRequestNotFoundException::withId($requestId);
                }
                $model->update($data);
            } else {
                $model = HolidayRequestModel::create($data);
                $requestId = $model->id;
            }
        });

        return $this->findByIdOrFail(HolidayRequestId::make($requestId));
    }

    public function findById(HolidayRequestId $id): ?HolidayRequest
    {
        $model = HolidayRequestModel::find($id->value());

        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
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
        return HolidayRequestModel::where('user_id', $userId->value())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (HolidayRequestModel $model) => $this->toDomainEntity($model))
            ->toArray();
    }

    /**
     * @return HolidayRequest[]
     */
    public function findPending(): array
    {
        return HolidayRequestModel::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn (HolidayRequestModel $model) => $this->toDomainEntity($model))
            ->toArray();
    }

    /**
     * @return HolidayRequest[]
     */
    public function findApproved(): array
    {
        return HolidayRequestModel::where('status', 'approved')
            ->orderBy('start_date', 'asc')
            ->get()
            ->map(fn (HolidayRequestModel $model) => $this->toDomainEntity($model))
            ->toArray();
    }

    /**
     * @return HolidayRequest[]
     */
    public function findAll(): array
    {
        return HolidayRequestModel::orderBy('created_at', 'desc')
            ->get()
            ->map(fn (HolidayRequestModel $model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function hasOverlapping(UserId $userId, DateRange $range, ?HolidayRequestId $excludeId = null): bool
    {
        $query = HolidayRequestModel::where('user_id', $userId->value())
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($range) {
                $q->where('start_date', '<=', $range->endDateFormatted())
                    ->where('end_date', '>=', $range->startDateFormatted());
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId->value());
        }

        return $query->exists();
    }

    public function delete(HolidayRequestId $id): bool
    {
        return HolidayRequestModel::where('id', $id->value())->delete() > 0;
    }

    private function toDomainEntity(HolidayRequestModel $model): HolidayRequest
    {
        return HolidayRequest::fromPrimitives([
            'id' => $model->id,
            'user_id' => $model->user_id,
            'start_date' => $model->start_date->format('Y-m-d'),
            'end_date' => $model->end_date->format('Y-m-d'),
            'status' => $model->status,
            'created_at' => $model->created_at?->toDateTimeString(),
            'updated_at' => $model->updated_at?->toDateTimeString(),
        ]);
    }
}
