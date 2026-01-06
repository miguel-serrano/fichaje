<?php

namespace App\DDD\TimeTracking\Domain;

use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
use App\DDD\User\Domain\ValueObjects\UserId;
use Carbon\Carbon;
use DateTime;

final class TimeEntry
{
    private ?TimeEntryId $id;

    private UserId $userId;

    private DateTime $entrada;

    private ?DateTime $salida;

    private bool $autoClosed;

    private ?string $autoCloseReason;

    private function __construct(
        ?TimeEntryId $id,
        UserId $userId,
        DateTime $entrada,
        ?DateTime $salida,
        bool $autoClosed = false,
        ?string $autoCloseReason = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->entrada = $entrada;
        $this->salida = $salida;
        $this->autoClosed = $autoClosed;
        $this->autoCloseReason = $autoCloseReason;
    }

    public static function create(UserId $userId): self
    {
        return new self(
            null,
            $userId,
            Carbon::now()->toDateTime(),
            null,
            false,
            null
        );
    }

    public static function fromPrimitives(
        ?int $id,
        int $userId,
        string $entrada,
        ?string $salida,
        bool $autoClosed = false,
        ?string $autoCloseReason = null
    ): self {
        return new self(
            $id ? new TimeEntryId($id) : null,
            new UserId($userId),
            new DateTime($entrada),
            $salida ? new DateTime($salida) : null,
            $autoClosed,
            $autoCloseReason
        );
    }

    public function id(): ?TimeEntryId
    {
        return $this->id;
    }

    public function setId(TimeEntryId $id): void
    {
        $this->id = $id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function entrada(): DateTime
    {
        return $this->entrada;
    }

    public function salida(): ?DateTime
    {
        return $this->salida;
    }

    public function cerrar(): void
    {
        $this->salida = Carbon::now()->toDateTime();
    }

    public function cerrarConFecha(DateTime $fechaCierre, bool $autoClosed = false, ?string $autoCloseReason = null): void
    {
        $this->salida = $fechaCierre;
        $this->autoClosed = $autoClosed;
        $this->autoCloseReason = $autoCloseReason;
    }

    public function isAbierto(): bool
    {
        return $this->salida === null;
    }

    public function isAutoClosed(): bool
    {
        return $this->autoClosed;
    }

    public function autoCloseReason(): ?string
    {
        return $this->autoCloseReason;
    }

    public function segundosTrabajados(): int
    {
        if ($this->entrada && $this->salida) {
            return $this->salida->getTimestamp() - $this->entrada->getTimestamp();
        }

        // Si está abierto, calcular con la hora actual (tiempo teórico)
        if ($this->entrada && $this->salida === null) {
            return time() - $this->entrada->getTimestamp();
        }

        return 0;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ? $this->id->value() : null,
            'user_id' => $this->userId->value(),
            'entrada' => $this->entrada->format('Y-m-d H:i:s'),
            'salida' => $this->salida ? $this->salida->format('Y-m-d H:i:s') : null,
            'auto_closed' => $this->autoClosed,
            'auto_close_reason' => $this->autoCloseReason,
        ];
    }
}
