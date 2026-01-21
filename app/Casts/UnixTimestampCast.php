<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast para convertir automáticamente campos de timestamp Unix (INT) en Eloquent.
 *
 * Este cast mantiene el valor como entero pero proporciona métodos helper
 * para formateo y conversión cuando se necesite.
 */
class UnixTimestampCast implements CastsAttributes
{
    /**
     * Obtiene el valor del atributo.
     *
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if (null === $value) {
            return null;
        }

        return (int) $value;
    }

    /**
     * Establece el valor del atributo para almacenar.
     *
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if (null === $value) {
            return null;
        }

        // Si es un entero, usarlo directamente
        if (is_int($value)) {
            return $value;
        }

        // Si es un string numérico, convertir
        if (is_numeric($value)) {
            return (int) $value;
        }

        // Si es un objeto DateTime, obtener el timestamp
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        // Si es un string de fecha, parsear
        if (is_string($value)) {
            $timestamp = strtotime($value);

            if (false === $timestamp) {
                throw new \InvalidArgumentException("No se puede parsear la fecha: {$value}");
            }

            return $timestamp;
        }

        throw new \InvalidArgumentException(sprintf('Tipo no soportado para UnixTimestampCast: %s', gettype($value)));
    }
}
