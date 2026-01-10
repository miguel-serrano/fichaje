<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Interface;

use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Holiday\Domain\ValueObjects\DateRange;
use App\DDD\Holiday\Domain\ValueObjects\HolidayRequestId;
use App\DDD\User\Domain\ValueObjects\UserId;

interface HolidayRepositoryInterface
{
    public function save(HolidayRequest $request): HolidayRequest;

    public function findById(HolidayRequestId $id): ?HolidayRequest;

    public function findByIdOrFail(HolidayRequestId $id): HolidayRequest;

    /**
     * @return HolidayRequest[]
     */
    public function findByUserId(UserId $userId): array;

    /**
     * @return HolidayRequest[]
     */
    public function findPending(): array;

    /**
     * @return HolidayRequest[]
     */
    public function findAll(): array;

    public function hasOverlapping(UserId $userId, DateRange $range, ?HolidayRequestId $excludeId = null): bool;

    public function delete(HolidayRequestId $id): bool;
}
