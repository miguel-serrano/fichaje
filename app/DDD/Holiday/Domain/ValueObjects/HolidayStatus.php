<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\ValueObjects;

enum HolidayStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function isPending(): bool
    {
        return self::Pending === $this;
    }

    public function isApproved(): bool
    {
        return self::Approved === $this;
    }

    public function isRejected(): bool
    {
        return self::Rejected === $this;
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Approved => 'Aprobada',
            self::Rejected => 'Rechazada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'orange',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }
}
