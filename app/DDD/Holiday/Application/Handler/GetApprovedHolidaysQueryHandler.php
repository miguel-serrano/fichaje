<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Holiday\Application\Query\GetApprovedHolidaysQuery;
use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class GetApprovedHolidaysQueryHandler
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @return HolidayRequest[]
     */
    public function handle(GetApprovedHolidaysQuery $query): array
    {
        $this->ensureUserIsAdmin($query->authenticatedUserId);

        return $this->holidayRepository->findApproved();
    }

    private function ensureUserIsAdmin(int $userId): void
    {
        $user = $this->userRepository->findByIdOrFail(new UserId($userId));

        if (!$user->isAdmin()) {
            throw new UnauthorizedException('Solo los administradores pueden ver las solicitudes aprobadas');
        }
    }
}
