<?php
namespace App\DDD\User\Domain\Entity;

use App\DDD\RegistroHorario\Domain\RegistroHorario;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;
use Exception;

final class User {
    private ?UserId $id;
    private Uuid $uuid;
    private Email $email;
    private string $name;
    private bool $isActive;
    /** @var RegistroHorario[] */
    private array $registrosHorarios;

    private function __construct(
        ?UserId $id,
        Uuid $uuid,
        Email $email,
        string $name,
        bool $isActive = true,
        array $registrosHorarios = []
    ) {
        $this->id = $id;
        $this->uuid = $uuid;
        $this->email = $email;
        $this->name = $name;
        $this->isActive = $isActive;
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
        array $registrosHorarios = []
    ): self {
        $user = new self(
            $id !== null ? new UserId($id) : null,
            new Uuid($uuid),
            new Email($email),
            $name,
            $isActive
        );

        foreach ($registrosHorarios as $registro) {
            $user->addRegistroHorario(RegistroHorario::fromPrimitives(
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

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    /** @return RegistroHorario[] */
    public function registrosHorarios(): array
    {
        return $this->registrosHorarios;
    }

    public function addRegistroHorario(RegistroHorario $registroHorario): void
    {
        $this->registrosHorarios[] = $registroHorario;
    }

    public function ficharEntrada(): void
    {
        if ($this->getRegistroAbierto()) {
            throw new Exception('Ya existe un registro de entrada abierto.');
        }

        if (!$this->id()) {
            throw new Exception('No se puede fichar la entrada para un usuario no guardado.');
        }

        $this->addRegistroHorario(
            RegistroHorario::create($this->id())
        );
    }

    public function ficharSalida(): void
    {
        $registroAbierto = $this->getRegistroAbierto();
        if (!$registroAbierto) {
            throw new Exception('No existe un registro de entrada abierto para cerrar.');
        }

        $registroAbierto->cerrar();
    }

    private function getRegistroAbierto(): ?RegistroHorario
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
            'registros_horarios' => array_map(function (RegistroHorario $registro) {
                return $registro->toArray();
            }, $this->registrosHorarios)
        ];
    }
}