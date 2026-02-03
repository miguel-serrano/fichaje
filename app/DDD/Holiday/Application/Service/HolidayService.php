<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Service;

use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Holiday\Domain\Exceptions\OverlappingHolidayException;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\ValueObjects\DateRange;
use App\DDD\Holiday\Domain\ValueObjects\HolidayRequestId;
use App\DDD\Shared\Domain\Event\EventBusInterface;
use App\DDD\User\Domain\Entity\User;

final class HolidayService
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
        private EventBusInterface $eventBus,
    ) {
    }

    public function createRequest(User $user, DateRange $dateRange): DateRange
    {
        if ($this->holidayRepository->hasOverlapping($user->id(), $dateRange)) {
            throw OverlappingHolidayException::forDateRange($dateRange);
        }

        $holidayRequest = HolidayRequest::create($user->id(), $dateRange);

        $this->holidayRepository->save($holidayRequest);

        $this->eventBus->publish(...$holidayRequest->pullDomainEvents());

        return $dateRange;
    }

    public function approve(HolidayRequestId $holidayRequestId): HolidayRequest
    {
        $holidayRequest = $this->holidayRepository->findByIdOrFail($holidayRequestId);

        $holidayRequest->approve();

        $this->holidayRepository->save($holidayRequest);

        $this->eventBus->publish(...$holidayRequest->pullDomainEvents());

        return $holidayRequest;
    }

    public function reject(HolidayRequestId $holidayRequestId): HolidayRequest
    {
        $holidayRequest = $this->holidayRepository->findByIdOrFail($holidayRequestId);

        $holidayRequest->reject();

        $this->holidayRepository->save($holidayRequest);

        $this->eventBus->publish(...$holidayRequest->pullDomainEvents());

        return $holidayRequest;
    }
}
