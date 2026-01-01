<?php

namespace App\DDD\RegistroHorario\Domain;

use App\DDD\RegistroHorario\Domain\ValueObjects\RegistroHorarioId;
use App\DDD\User\Domain\ValueObjects\UserId;
use Carbon\Carbon;
use DateTime;

final class RegistroHorario
{
    private ?RegistroHorarioId $id;
    private UserId $userId;
    private DateTime $entrada;
    private ?DateTime $salida;

    private function __construct(
        ?RegistroHorarioId $id,
        UserId $userId,
        DateTime $entrada,
        ?DateTime $salida
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->entrada = $entrada;
        $this->salida = $salida;
    }

    public static function create(UserId $userId): self
    {
        return new self(
            null,
            $userId,
            Carbon::now()->toDateTime(),
            null
        );
    }

    public static function fromPrimitives(
        ?int $id,
        int $userId,
        string $entrada,
        ?string $salida
    ): self {
        return new self(
            $id ? new RegistroHorarioId($id) : null,
            new UserId($userId),
            new DateTime($entrada),
            $salida ? new DateTime($salida) : null
        );
    }

    public function id(): ?RegistroHorarioId
    {
        return $this->id;
    }

    public function setId(RegistroHorarioId $id): void
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

    public function isAbierto(): bool
    {
        return $this->salida === null;
    }

    public function segundosTrabajados(): int
    {
        if ($this->entrada && $this->salida) {
            return $this->salida->getTimestamp() - $this->entrada->getTimestamp();
        }
        return 0;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ? $this->id->getValue() : null,
            'user_id' => $this->userId->getValue(),
            'entrada' => $this->entrada->format('Y-m-d H:i:s'),
            'salida' => $this->salida ? $this->salida->format('Y-m-d H:i:s') : null,
        ];
    }
}

