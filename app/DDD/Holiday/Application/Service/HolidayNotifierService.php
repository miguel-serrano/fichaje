<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Service;

use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Holiday\Domain\ValueObjects\DateRange;
use App\DDD\Notification\Application\Service\NotificationService;
use App\DDD\Notification\Domain\Entity\Notification;
use App\DDD\Notification\Domain\ValueObjects\NotificationType;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

final class HolidayNotifierService
{
    public function __construct(
        private NotificationService $notificationService,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function notifyHolidayRequested(User $requester, DateRange $dateRange): void
    {
        $admins = $this->userRepository->findAdmins();

        $notification = Notification::create(
            NotificationType::HolidayRequested,
            'Solicitud de vacaciones',
            sprintf(
                '%s ha solicitado vacaciones del %s al %s',
                $requester->name()->value(),
                $dateRange->startDateFormatted('d/m/Y'),
                $dateRange->endDateFormatted('d/m/Y')
            ),
            [
                'user_id' => $requester->id()->value(),
                'user_name' => $requester->name()->value(),
                'start_date' => $dateRange->startDateFormatted(),
                'end_date' => $dateRange->endDateFormatted(),
            ]
        );

        foreach ($admins as $admin) {
            $this->notificationService->notify($admin, $notification);
        }
    }

    public function notifyHolidayApproved(HolidayRequest $holidayRequest): void
    {
        $user = $this->userRepository->findByIdOrFail($holidayRequest->userId());

        $notification = Notification::create(
            NotificationType::HolidayApproved,
            'Vacaciones aprobadas',
            sprintf(
                'Tu solicitud de vacaciones del %s al %s ha sido aprobada',
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

    public function notifyHolidayRejected(HolidayRequest $holidayRequest): void
    {
        $user = $this->userRepository->findByIdOrFail($holidayRequest->userId());

        $notification = Notification::create(
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
