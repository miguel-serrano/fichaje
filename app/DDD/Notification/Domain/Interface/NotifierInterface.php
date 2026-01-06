<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain\Interface;

use App\DDD\Notification\Domain\Channel;
use App\DDD\Notification\Domain\Notification;
use App\DDD\User\Domain\Entity\User;

interface NotifierInterface
{
    public function send(User $user, Notification $notification): void;

    public function supports(Channel $channel): bool;
}
