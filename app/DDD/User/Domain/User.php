<?php
namespace App\DDD\User\Domain;
final class User {
    private ?UserId $id;
    private Uuid $uuid;
    private Email $email;
    private string $name;
    private bool $isActive;
    private function __construct(?UserId $id, Uuid $uuid, Email $email, string $name, bool $isActive = true) {
        $this->id = $id;
        $this->uuid = $uuid;
        $this->email = $email;
        $this->name = $name;
        $this->isActive = $isActive;
    }
    public static function create(Email $email, string $name): self {
        return new self(null, Uuid::generate(), $email, $name);
    }
    public static function fromPrimitives($id, string $uuid, string $email, string $name, bool $isActive): self {
        return new self($id !== null ? new UserId($id) : null, new Uuid($uuid), new Email($email), $name, $isActive);
    }
    public function id(): ?UserId { return $this->id; }
    public function uuid(): Uuid { return $this->uuid; }
    public function email(): Email { return $this->email; }
    public function name(): string { return $this->name; }
    public function isActive(): bool { return $this->isActive; }
    public function deactivate(): void { $this->isActive = false; }
    public function toArray(): array {
        return [
            'id' => $this->id ? $this->id->getValue() : null,
            'uuid' => $this->uuid->getValue(),
            'email' => $this->email->getValue(),
            'name' => $this->name,
            'is_active' => $this->isActive
        ];
    }
}