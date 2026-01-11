<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Holiday\Application\Command\RejectHolidayRequestCommand;
use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\ValueObjects\HolidayRequestId;
use App\DDD\Notification\Application\NotificationService;
use App\DDD\Notification\Domain\Notification;
use App\DDD\Notification\Domain\NotificationType;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class RejectHolidayRequestCommandHandler
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
        private UserRepositoryInterface $userRepository,
        private NotificationService $notificationService,
    ) {
    }

    public function handle(RejectHolidayRequestCommand $command): HolidayRequest
    {
        $this->ensureUserIsAdmin($command->authenticatedUserId);

        $holidayRequest = $this->holidayRepository->findByIdOrFail(
            new HolidayRequestId($command->holidayRequestId)
        );

        $holidayRequest->reject();
        $savedRequest = $this->holidayRepository->save($holidayRequest);

        $this->notifyUser($holidayRequest);

        return $savedRequest;
    }

    private function ensureUserIsAdmin(int $userId): void
    {
        $user = $this->userRepository->findByIdOrFail(new UserId($userId));

        if (!$user->isAdmin()) {
            throw new UnauthorizedException('Solo los administradores pueden rechazar solicitudes de vacaciones');
        }
    }

    private function notifyUser(HolidayRequest $holidayRequest): void
    {
        $user = $this->userRepository->findByIdOrFail($holidayRequest->userId());

        $notification = new Notification(
            NotificationType::HolidayRejected,
            'Vacaciones rechazadas',
            sprintf(
                'Tu solicitud de vacaciones del %s al %s ha sido rechazada',
                $holidayRequest->dateRange()->startDateFormatted('d/m/Y'),
                $holidayRequest->dateRange()->endDateFormatted('d/m/Y')
            ),
            [
                'holiday_request_id' => $holidayRequest->id()->value(),
                'start_date' => $holidayRequest->dateRange()->startDateFormatted(),
                'end_date' => $holidayRequest->dateRange()->endDateFormatted(),
            ]
        );

        $this->notificationService->notify($user, $notification);
    }
}
