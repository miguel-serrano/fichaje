<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Entity;

use App\DDD\Holiday\Domain\ValueObjects\DateRange;
use App\DDD\Holiday\Domain\ValueObjects\HolidayRequestId;
use App\DDD\Holiday\Domain\ValueObjects\HolidayStatus;
use App\DDD\Shared\Domain\ValueObject\UnixTimestamp;
use App\DDD\User\Domain\ValueObjects\UserId;

final class HolidayRequest
{
    private function __construct(
        private ?HolidayRequestId $id,
        private UserId $userId,
        private DateRange $dateRange,
        private HolidayStatus $status,
        /** Timestamp Unix de creación. */
        private ?int $createdAt = null,
        /** Timestamp Unix de última actualización. */
        private ?int $updatedAt = null,
    ) {
    }

    public static function create(UserId $userId, DateRange $dateRange): self
    {
        $now = UnixTimestamp::now()->value();

        return new self(
            null,
            $userId,
            $dateRange,
            HolidayStatus::Pending,
            $now,
            $now
        );
    }

    /**
     * @param array{id: int, user_id: int, start_date: int, end_date: int, status: string, created_at: ?int, updated_at: ?int} $data
     */
    public static function fromPrimitives(array $data): self
    {
        return new self(
            new HolidayRequestId((int) $data['id']),
            new UserId((int) $data['user_id']),
            DateRange::fromPersistence((int) $data['start_date'], (int) $data['end_date']),
            HolidayStatus::from($data['status']),
            isset($data['created_at']) ? (int) $data['created_at'] : null,
            isset($data['updated_at']) ? (int) $data['updated_at'] : null
        );
    }

    public function id(): ?HolidayRequestId
    {
        return $this->id;
    }

    public function setId(HolidayRequestId $id): void
    {
        $this->id = $id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function dateRange(): DateRange
    {
        return $this->dateRange;
    }

    public function status(): HolidayStatus
    {
        return $this->status;
    }

    public function createdAt(): ?int
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?int
    {
        return $this->updatedAt;
    }

    public function createdAtFormatted(string $format = 'Y-m-d H:i:s'): ?string
    {
        return null !== $this->createdAt ? date($format, $this->createdAt) : null;
    }

    public function updatedAtFormatted(string $format = 'Y-m-d H:i:s'): ?string
    {
        return null !== $this->updatedAt ? date($format, $this->updatedAt) : null;
    }

    public function approve(): void
    {
        $this->status = HolidayStatus::Approved;
        $this->updatedAt = UnixTimestamp::now()->value();
    }

    public function reject(): void
    {
        $this->status = HolidayStatus::Rejected;
        $this->updatedAt = UnixTimestamp::now()->value();
    }

    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    public function isApproved(): bool
    {
        return $this->status->isApproved();
    }

    public function isRejected(): bool
    {
        return $this->status->isRejected();
    }

    /**
     * @return array{id: ?int, user_id: int, start_date: int, end_date: int, status: string, created_at: ?int, updated_at: ?int}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id?->value(),
            'user_id' => (int) $this->userId->value(),
            'start_date' => $this->dateRange->startDate(),
            'end_date' => $this->dateRange->endDate(),
            'status' => $this->status->value,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
