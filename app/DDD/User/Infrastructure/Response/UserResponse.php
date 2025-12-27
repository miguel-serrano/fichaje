<?php

namespace App\DDD\User\Infrastructure\Response;

class UserResponse
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $uuid,
        public readonly string $email,
        public readonly string $name,
        public readonly bool $isActive
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['uuid'],
            $data['email'],
            $data['name'],
            $data['is_active']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'email' => $this->email,
            'name' => $this->name,
            'is_active' => $this->isActive
        ];
    }
}
