<?php

namespace App\DDD\User\Application\Command;

class DeleteUserCommand
{
    public function __construct(
        private string $id
    ) {}

    public function getId(): string
    {
        return $this->id;
    }
}
