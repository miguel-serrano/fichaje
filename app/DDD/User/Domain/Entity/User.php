<?php

namespace App\DDD\User\Domain\Entity;

use App\DDD\TimeTracking\Domain\TimeEntry;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\RememberToken;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;
use Exception;

final class User
{
    private ?UserId $id;

    private Uuid $uuid;

    private Email $email;

    private string $name;

    private bool $isActive;

    private RememberToken $rememberToken;

    /** @var TimeEntry[] */
    private array $registrosHorarios;

    private function __construct(
        ?UserId $id,
        Uuid $uuid,
        Email $email,
        string $name,
        bool $isActive = true,
        ?RememberToken $rememberToken = null,
        array $registrosHorarios = []
    ) {
        $this->id = $id;
        $this->uuid = $uuid;
        $this->email = $email;
        $this->name = $name;
        $this->isActive = $isActive;
        $this->rememberToken = $rememberToken ?? new RememberToken(null);
        $this->registrosHorarios = $registrosHorarios;
    }

    public static function create(Email $email, string $name): self
    {
        return new self(null, Uuid::generate(), $email, $name);
    }

    public static function fromPrimitives(
        ?int $id,
        string $uuid,
        string $email,
        string $name,
        bool $isActive,
        ?string $rememberToken = null,
        array $registrosHorarios = []
    ): self {
        $user = new self(
            $id !== null ? new UserId($id) : null,
            new Uuid($uuid),
            new Email($email),
            $name,
            $isActive,
            new RememberToken($rememberToken)
        );

        foreach ($registrosHorarios as $registro) {
            $user->addRegistroHorario(TimeEntry::fromPrimitives(
                $registro['id'],
                $registro['user_id'],
                $registro['entrada'],
                $registro['salida']
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

    public function rememberToken(): RememberToken
    {
        return $this->rememberToken;
    }

    public function isAdmin(): bool
    {
        return $this->rememberToken->isAdmin();
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

        $this->addRegistroHorario(
            TimeEntry::create($this->id())
        );
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
            'registros_horarios' => array_map(function (TimeEntry $registro) {
                return $registro->toArray();
            }, $this->registrosHorarios),
        ];
    }
}
