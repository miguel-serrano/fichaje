<?php

namespace App\DDD\User\Domain\Entity;

use App\DDD\TimeTracking\Domain\Exceptions\DailyTimeEntryLimitExceededException;
use App\DDD\TimeTracking\Domain\TimeEntry;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;
use Carbon\Carbon;
use Exception;

final class User
{
    private ?UserId $id;

    private Uuid $uuid;

    private Email $email;

    private string $name;

    private bool $isActive;

    private bool $isAdmin;

    /** @var TimeEntry[] */
    private array $registrosHorarios;

    private function __construct(
        ?UserId $id,
        Uuid $uuid,
        Email $email,
        string $name,
        bool $isActive = true,
        bool $isAdmin = false,
        array $registrosHorarios = []
    ) {
        $this->id = $id;
        $this->uuid = $uuid;
        $this->email = $email;
        $this->name = $name;
        $this->isActive = $isActive;
        $this->isAdmin = $isAdmin;
        $this->registrosHorarios = $registrosHorarios;
    }

    public static function create(Email $email, string $name): self
    {
        return new self(null, Uuid::generate(), $email, $name, false);
    }

    public static function fromPrimitives(
        ?int $id,
        string $uuid,
        string $email,
        string $name,
        bool $isActive,
        bool $isAdmin = false,
        array $registrosHorarios = []
    ): self {
        $user = new self(
            $id !== null ? new UserId($id) : null,
            new Uuid($uuid),
            new Email($email),
            $name,
            $isActive,
            $isAdmin
        );

        foreach ($registrosHorarios as $registro) {
            $user->addRegistroHorario(TimeEntry::fromPrimitives(
                $registro['id'],
                $registro['user_id'],
                $registro['entrada'],
                $registro['salida'],
                (bool) ($registro['auto_closed'] ?? false),
                $registro['auto_close_reason'] ?? null
            ));
        }

        return $user;
    }

    public function id(): ?UserId
    {
        return $this->id;
    }

    public function uuid(): Uuid
    {
        return $this->uuid;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function toggleActive(): bool
    {
        $this->isActive = ! $this->isActive;

        return $this->isActive;
    }

    /** @return TimeEntry[] */
    public function registrosHorarios(): array
    {
        return $this->registrosHorarios;
    }

    public function addRegistroHorario(TimeEntry $registroHorario): void
    {
        $this->registrosHorarios[] = $registroHorario;
    }

    public function ficharEntrada(): void
    {
        if ($this->getRegistroAbierto()) {
            throw new Exception('Ya existe un registro de entrada abierto.');
        }

        if (! $this->id()) {
            throw new Exception('No se puede fichar la entrada para un usuario no guardado.');
        }

        // Validar límite diario (solo para usuarios no admin)
        if (! $this->isAdmin()) {
            $registrosHoy = $this->countTodayEntries();
            if ($registrosHoy >= DailyTimeEntryLimitExceededException::MAX_DAILY_ENTRIES) {
                throw new DailyTimeEntryLimitExceededException($registrosHoy);
            }
        }

        $this->addRegistroHorario(
            TimeEntry::create($this->id())
        );
    }

    private function countTodayEntries(): int
    {
        $today = Carbon::today();
        $count = 0;

        foreach ($this->registrosHorarios as $registro) {
            $entradaCarbon = Carbon::instance($registro->entrada());
            if ($entradaCarbon->isSameDay($today)) {
                $count++;
            }
        }

        return $count;
    }

    public function ficharSalida(): void
    {
        $registroAbierto = $this->getRegistroAbierto();
        if (! $registroAbierto) {
            throw new Exception('No existe un registro de entrada abierto para cerrar.');
        }

        $registroAbierto->cerrar();
    }

    private function getRegistroAbierto(): ?TimeEntry
    {
        foreach ($this->registrosHorarios as $registro) {
            if ($registro->isAbierto()) {
                return $registro;
            }
        }

        return null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ? $this->id->getValue() : null,
            'uuid' => $this->uuid->getValue(),
            'email' => $this->email->getValue(),
            'name' => $this->name(),
            'is_active' => $this->isActive(),
            'is_admin' => $this->isAdmin(),
            'registros_horarios' => array_map(function (TimeEntry $registro) {
                return $registro->toArray();
            }, $this->registrosHorarios),
        ];
    }
}
