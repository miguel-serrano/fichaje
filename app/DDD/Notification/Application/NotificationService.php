<?php

declare(strict_types=1);

namespace App\DDD\Notification\Application;

use App\DDD\Notification\Domain\Interface\NotifierInterface;
use App\DDD\Notification\Domain\Notification;
use App\DDD\User\Domain\Entity\User;

final class NotificationService
{
    /**
     * @param  NotifierInterface[]  $notifiers
     */
    public function __construct(private array $notifiers) {}

    public function notify(User $user, Notification $notification): void
    {
        foreach ($notification->channels() as $channel) {
            foreach ($this->notifiers as $notifier) {
                if ($notifier->supports($channel)) {
                    $notifier->send($user, $notification);
                }
            }
        }
    }
}
