<?php

namespace App\DDD\Shared\Domain\ValueObject;

use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Value Object para representar timestamps Unix (segundos desde epoch).
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @method static static make(int $value)
 * @method static static from(int $value)
 *
 * @extends IntValueObject<TKey, TValue>
 */
final class UnixTimestamp extends IntValueObject
{
    private const DEFAULT_FORMAT = 'Y-m-d H:i:s';

    private const DATE_FORMAT = 'Y-m-d';

    private const TIME_FORMAT = 'H:i:s';

    private const DEFAULT_TIMEZONE = 'Europe/Madrid';

    /**
     * Crea un UnixTimestamp con el tiempo actual.
     */
    public static function now(): self
    {
        return new self(time());
    }

    /**
     * Crea un UnixTimestamp desde un objeto DateTimeInterface.
     */
    public static function fromDateTime(\DateTimeInterface $dateTime): self
    {
        return new self($dateTime->getTimestamp());
    }

    /**
     * Crea un UnixTimestamp desde un string de fecha.
     *
     * @param string $datetime String en formato parseable por strtotime
     */
    public static function fromString(string $datetime): self
    {
        $timestamp = strtotime($datetime);

        if (false === $timestamp) {
            throw new \InvalidArgumentException("No se puede parsear la fecha: {$datetime}");
        }

        return new self($timestamp);
    }

    /**
     * Crea un UnixTimestamp desde un entero.
     */
    public static function fromInt(int $timestamp): self
    {
        return new self($timestamp);
    }

    /**
     * Crea un UnixTimestamp o null desde un valor nullable.
     */
    public static function fromNullableInt(?int $timestamp): ?self
    {
        return null !== $timestamp ? new self($timestamp) : null;
    }

    /**
     * Crea un UnixTimestamp representando la medianoche de una fecha.
     *
     * @param string $date Fecha en formato Y-m-d
     */
    public static function fromDateAtMidnight(string $date): self
    {
        $timestamp = strtotime($date.' 00:00:00');

        if (false === $timestamp) {
            throw new \InvalidArgumentException("No se puede parsear la fecha: {$date}");
        }

        return new self($timestamp);
    }

    /**
     * Convierte a DateTimeImmutable.
     */
    public function toDateTime(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->setTimestamp($this->value);
    }

    /**
     * Convierte a DateTimeImmutable con timezone específico.
     */
    public function toDateTimeWithTimezone(string $timezone = self::DEFAULT_TIMEZONE): \DateTimeImmutable
    {
        return $this->toDateTime()->setTimezone(new \DateTimeZone($timezone));
    }

    /**
     * Convierte a Carbon.
     */
    public function toCarbon(): Carbon
    {
        return Carbon::createFromTimestamp($this->value);
    }

    /**
     * Convierte a Carbon con timezone específico.
     */
    public function toCarbonWithTimezone(string $timezone = self::DEFAULT_TIMEZONE): Carbon
    {
        return Carbon::createFromTimestamp($this->value, $timezone);
    }

    /**
     * Formatea el timestamp a string con el formato especificado.
     *
     * @param string      $format   Formato de fecha (compatible con date())
     * @param string|null $timezone Timezone para mostrar (null = timezone de PHP)
     */
    public function format(string $format = self::DEFAULT_FORMAT, ?string $timezone = null): string
    {
        if (null !== $timezone) {
            return $this->toCarbonWithTimezone($timezone)->format($format);
        }

        return date($format, $this->value);
    }

    /**
     * Formatea como fecha (Y-m-d).
     */
    public function toDateString(?string $timezone = null): string
    {
        return $this->format(self::DATE_FORMAT, $timezone);
    }

    /**
     * Formatea como hora (H:i:s).
     */
    public function toTimeString(?string $timezone = null): string
    {
        return $this->format(self::TIME_FORMAT, $timezone);
    }

    /**
     * Formatea para mostrar al usuario en timezone local (Europe/Madrid).
     */
    public function toLocalFormat(string $format = self::DEFAULT_FORMAT): string
    {
        return $this->format($format, self::DEFAULT_TIMEZONE);
    }

    /**
     * Calcula la diferencia en segundos con otro timestamp.
     */
    public function diffInSeconds(self $other): int
    {
        return abs($this->value - $other->value);
    }

    /**
     * Calcula la diferencia en segundos desde otro timestamp (puede ser negativo).
     */
    public function secondsSince(self $other): int
    {
        return $this->value - $other->value;
    }

    /**
     * Añade segundos al timestamp.
     */
    public function addSeconds(int $seconds): self
    {
        return new self($this->value + $seconds);
    }

    /**
     * Resta segundos al timestamp.
     */
    public function subSeconds(int $seconds): self
    {
        return new self($this->value - $seconds);
    }

    /**
     * Añade días al timestamp.
     */
    public function addDays(int $days): self
    {
        return $this->addSeconds($days * 86400);
    }

    /**
     * Verifica si es el mismo día que otro timestamp.
     */
    public function isSameDay(self $other): bool
    {
        return $this->toDateString() === $other->toDateString();
    }

    /**
     * Verifica si el timestamp es de hoy.
     */
    public function isToday(): bool
    {
        return $this->toDateString() === date(self::DATE_FORMAT);
    }

    /**
     * Verifica si el timestamp es anterior a otro.
     */
    public function isBefore(self $other): bool
    {
        return $this->value < $other->value;
    }

    /**
     * Verifica si el timestamp es posterior a otro.
     */
    public function isAfter(self $other): bool
    {
        return $this->value > $other->value;
    }

    /**
     * Verifica si el timestamp es anterior o igual a otro.
     */
    public function isBeforeOrEqual(self $other): bool
    {
        return $this->value <= $other->value;
    }

    /**
     * Verifica si el timestamp es posterior o igual a otro.
     */
    public function isAfterOrEqual(self $other): bool
    {
        return $this->value >= $other->value;
    }

    /**
     * Obtiene el inicio del día (medianoche).
     */
    public function startOfDay(): self
    {
        return self::fromDateAtMidnight($this->toDateString());
    }

    /**
     * Obtiene el final del día (23:59:59).
     */
    public function endOfDay(): self
    {
        $midnight = $this->startOfDay();

        return new self($midnight->value + 86399); // 23:59:59
    }

    /**
     * Calcula los días completos de diferencia con otro timestamp.
     */
    public function diffInDays(self $other): int
    {
        return (int) floor(abs($this->value - $other->value) / 86400);
    }

    /**
     * Verifica si está en el pasado.
     */
    public function isPast(): bool
    {
        return $this->value < time();
    }

    /**
     * Verifica si está en el futuro.
     */
    public function isFuture(): bool
    {
        return $this->value > time();
    }
}
