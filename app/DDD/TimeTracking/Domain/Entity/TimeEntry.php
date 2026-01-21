<?php

namespace App\DDD\TimeTracking\Domain\Entity;

use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
use App\DDD\User\Domain\ValueObjects\UserId;

final class TimeEntry
{
    private ?TimeEntryId $id;

    private UserId $userId;

    /**
     * Timestamp Unix de la hora de entrada.
     */
    private int $startTime;

    /**
     * Timestamp Unix de la hora de salida (nullable si está abierto).
     */
    private ?int $endTime;

    private bool $autoClosed;

    private ?string $autoCloseReason;

    private function __construct(
        ?TimeEntryId $id,
        UserId $userId,
        int $startTime,
        ?int $endTime,
        bool $autoClosed = false,
        ?string $autoCloseReason = null,
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->autoClosed = $autoClosed;
        $this->autoCloseReason = $autoCloseReason;
    }

    public static function create(UserId $userId): self
    {
        return new self(
            null,
            $userId,
            time(),
            null,
            false,
            null
        );
    }

    /**
     * Crea una entidad desde primitivos (para reconstruir desde persistencia).
     *
     * @param int|null    $id              ID del registro
     * @param int         $userId          ID del usuario
     * @param int         $startTime       Timestamp Unix de entrada
     * @param int|null    $endTime         Timestamp Unix de salida (null si abierto)
     * @param bool        $autoClosed      Si fue cerrado automáticamente
     * @param string|null $autoCloseReason Razón del cierre automático
     */
    public static function fromPrimitives(
        ?int $id,
        int $userId,
        int $startTime,
        ?int $endTime,
        bool $autoClosed = false,
        ?string $autoCloseReason = null,
    ): self {
        return new self(
            $id ? new TimeEntryId($id) : null,
            new UserId($userId),
            $startTime,
            $endTime,
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

    /**
     * Obtiene el timestamp Unix de la hora de entrada.
     */
    public function startTime(): int
    {
        return $this->startTime;
    }

    /**
     * Obtiene el timestamp Unix de la hora de salida.
     */
    public function endTime(): ?int
    {
        return $this->endTime;
    }

    /**
     * Formatea la hora de entrada para mostrar.
     */
    public function startTimeFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format, $this->startTime);
    }

    /**
     * Formatea la hora de salida para mostrar.
     */
    public function endTimeFormatted(string $format = 'Y-m-d H:i:s'): ?string
    {
        return null !== $this->endTime ? date($format, $this->endTime) : null;
    }

    public function close(): void
    {
        $this->endTime = time();
    }

    /**
     * Cierra la entrada en un momento específico.
     *
     * @param int $closeTime Timestamp Unix del momento de cierre
     */
    public function closeAt(int $closeTime, bool $autoClosed = false, ?string $autoCloseReason = null): void
    {
        $this->endTime = $closeTime;
        $this->autoClosed = $autoClosed;
        $this->autoCloseReason = $autoCloseReason;
    }

    public function isOpen(): bool
    {
        return null === $this->endTime;
    }

    public function isAutoClosed(): bool
    {
        return $this->autoClosed;
    }

    public function autoCloseReason(): ?string
    {
        return $this->autoCloseReason;
    }

    /**
     * Calcula los segundos trabajados.
     * Si la entrada está abierta, calcula hasta el momento actual.
     */
    public function workedSeconds(): int
    {
        if ($this->startTime && $this->endTime) {
            return $this->endTime - $this->startTime;
        }

        if ($this->startTime && null === $this->endTime) {
            return time() - $this->startTime;
        }

        return 0;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ? $this->id->value() : null,
            'user_id' => $this->userId->value(),
            'entrada' => $this->startTime,
            'salida' => $this->endTime,
            'auto_closed' => $this->autoClosed,
            'auto_close_reason' => $this->autoCloseReason,
        ];
    }
}
