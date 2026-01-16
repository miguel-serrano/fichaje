<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain\ValueObjects;

enum Channel: string
{
    case Database = 'database';
    case Mail = 'mail';
    case Slack = 'slack';
}
