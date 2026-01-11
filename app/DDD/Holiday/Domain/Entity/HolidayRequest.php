<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Entity;

use App\DDD\Holiday\Domain\ValueObjects\DateRange;
use App\DDD\Holiday\Domain\ValueObjects\HolidayRequestId;
use App\DDD\Holiday\Domain\ValueObjects\HolidayStatus;
use App\DDD\User\Domain\ValueObjects\UserId;

final class HolidayRequest
{
    private ?HolidayRequestId $id;
    private UserId $userId;
    private DateRange $dateRange;
    private HolidayStatus $status;
    private ?\DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    private function __construct(
        ?HolidayRequestId $id,
        UserId $userId,
        DateRange $dateRange,
        HolidayStatus $status,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->dateRange = $dateRange;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(UserId $userId, DateRange $dateRange): self
    {
        return new self(
            null,
            $userId,
            $dateRange,
            HolidayStatus::Pending,
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );
    }

    /**
     * @param array{id: int, user_id: int, start_date: string, end_date: string, status: string, created_at: ?string, updated_at: ?string} $data
     */
    public static function fromPrimitives(array $data): self
    {
        return new self(
            new HolidayRequestId($data['id']),
            new UserId($data['user_id']),
            DateRange::fromStrings($data['start_date'], $data['end_date']),
            HolidayStatus::from($data['status']),
            isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null
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

    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function approve(): void
    {
        $this->status = HolidayStatus::Approved;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function reject(): void
    {
        $this->status = HolidayStatus::Rejected;
        $this->updatedAt = new \DateTimeImmutable();
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
     * @return array{id: ?int, user_id: int, start_date: string, end_date: string, status: string, created_at: ?string, updated_at: ?string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id?->value(),
            'user_id' => (int) $this->userId->value(),
            'start_date' => $this->dateRange->startDateFormatted(),
            'end_date' => $this->dateRange->endDateFormatted(),
            'status' => $this->status->value,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
