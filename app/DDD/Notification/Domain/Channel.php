<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain;

enum Channel: string
{
    case Database = 'database';
    case Mail = 'mail';
    case Slack = 'slack';
}
