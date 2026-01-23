<?php

namespace App\DDD\Authorization\Domain\ValueObjects;

use App\DDD\Shared\Domain\ValueObject\StringValueObject;

final class PermissionSlug extends StringValueObject
{
    protected function validate(): void
    {
        if (empty($this->value)) {
            throw new \InvalidArgumentException('El slug del permiso no puede estar vacío');
        }

        if (!preg_match('/^[a-z]+\.[a-z_]+$/', $this->value)) {
            throw new \InvalidArgumentException('El slug del permiso debe seguir el formato: contexto.acción (ej: user.create, timetracking.view_all)');
        }
    }

    public function context(): string
    {
        return explode('.', $this->value)[0];
    }

    public function action(): string
    {
        return explode('.', $this->value)[1];
    }
}
