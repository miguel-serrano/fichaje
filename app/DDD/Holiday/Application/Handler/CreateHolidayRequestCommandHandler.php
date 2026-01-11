<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Holiday\Application\Command\CreateHolidayRequestCommand;
use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Holiday\Domain\Exceptions\OverlappingHolidayException;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\ValueObjects\DateRange;
use App\DDD\Notification\Application\NotificationService;
use App\DDD\Notification\Domain\Notification;
use App\DDD\Notification\Domain\NotificationType;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class CreateHolidayRequestCommandHandler
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
        private UserRepositoryInterface $userRepository,
        private NotificationService $notificationService,
    ) {
    }

    public function handle(CreateHolidayRequestCommand $command): HolidayRequest
    {
        $userId = new UserId($command->userId);
        $dateRange = DateRange::fromStrings($command->startDate, $command->endDate);

        if ($this->holidayRepository->hasOverlapping($userId, $dateRange)) {
            throw new OverlappingHolidayException();
        }

        $holidayRequest = HolidayRequest::create($userId, $dateRange);
        $savedRequest = $this->holidayRepository->save($holidayRequest);

        $this->notifyAdmins($userId, $dateRange);

        return $savedRequest;
    }

    private function notifyAdmins(UserId $userId, DateRange $dateRange): void
    {
        $user = $this->userRepository->findByIdOrFail($userId);
        $admins = $this->userRepository->findAdmins();

        $notification = new Notification(
            NotificationType::HolidayRequested,
            'Solicitud de vacaciones',
            sprintf(
                '%s ha solicitado vacaciones del %s al %s',
                $user->name(),
                $dateRange->startDateFormatted('d/m/Y'),
                $dateRange->endDateFormatted('d/m/Y')
            ),
            [
                'user_id' => $userId->value(),
                'user_name' => $user->name(),
                'start_date' => $dateRange->startDateFormatted(),
                'end_date' => $dateRange->endDateFormatted(),
            ]
        );

        foreach ($admins as $admin) {
            $this->notificationService->notify($admin, $notification);
        }
    }
}
