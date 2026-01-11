<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Holiday\Application\Query\GetUserHolidaysQuery;
use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class GetUserHolidaysQueryHandler
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
    ) {
    }

    /**
     * @return HolidayRequest[]
     */
    public function handle(GetUserHolidaysQuery $query): array
    {
        return $this->holidayRepository->findByUserId(new UserId($query->userId));
    }
}
