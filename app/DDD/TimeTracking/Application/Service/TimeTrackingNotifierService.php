<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Service;

use App\DDD\Notification\Application\NotificationService;
use App\DDD\Notification\Domain\Entity\Notification;
use App\DDD\Notification\Domain\ValueObjects\Channel;
use App\DDD\Notification\Domain\ValueObjects\NotificationType;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use Carbon\Carbon;

final class TimeTrackingNotifierService
{
    public function __construct(
        private NotificationService $notificationService,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @param array<int, array<int, array<string, mixed>>> $closedByUser
     */
    public function notifyOrphanEntriesClosed(array $closedByUser): void
    {
        foreach ($closedByUser as $userId => $entries) {
            $user = $this->userRepository->findById(UserId::make($userId));
            if ($user) {
                $notification = Notification::create(
                    type: NotificationType::TimeEntryAutoClosed,
                    title: 'Fichaje cerrado automáticamente',
                    message: $this->buildOrphanClosedMessage($entries),
                    data: ['entries' => $entries],
                    channels: [Channel::Database]
                );
                $this->notificationService->notify($user, $notification);
            }
        }
    }

    public function notifyTimeEntryAutoClosed(User $user, array $entry): void
    {
        $notification = Notification::create(
            type: NotificationType::TimeEntryAutoClosed,
            title: 'Fichaje cerrado automáticamente',
            message: $this->buildOrphanClosedMessage([$entry]),
            data: ['entries' => [$entry]],
            channels: [Channel::Database]
        );
        $this->notificationService->notify($user, $notification);
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     */
    private function buildOrphanClosedMessage(array $entries): string
    {
        if (1 === count($entries)) {
            $entry = $entries[0];
            $checkIn = is_int($entry['entrada'])
                ? Carbon::createFromTimestamp($entry['entrada'])
                : Carbon::parse($entry['entrada']);
            $checkOut = is_int($entry['salida'])
                ? Carbon::createFromTimestamp($entry['salida'])
                : Carbon::parse($entry['salida']);
            $reason = 'max_hours_exceeded' === $entry['reason']
                ? 'al alcanzar el límite de 8 horas diarias'
                : 'al final del día';

            return sprintf(
                'Entrada: %s a las %s. Cerrado automáticamente a las %s %s.',
                $checkIn->format('d/m/Y'),
                $checkIn->format('H:i'),
                $checkOut->format('H:i'),
                $reason
            );
        }

        return sprintf('%d fichajes se cerraron automáticamente.', count($entries));
    }
}
