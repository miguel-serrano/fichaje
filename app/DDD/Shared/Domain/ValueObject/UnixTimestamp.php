<?php

namespace App\DDD\Shared\Domain\ValueObject;

use Carbon\Carbon;

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

    public static function now(): self
    {
        return new self(time());
    }

    public static function fromDateTime(\DateTimeInterface $dateTime): self
    {
        return new self($dateTime->getTimestamp());
    }

    public static function fromString(string $datetime): self
    {
        $timestamp = strtotime($datetime);

        if (false === $timestamp) {
            throw new \InvalidArgumentException("No se puede parsear la fecha: {$datetime}");
        }

        return new self($timestamp);
    }

    public static function fromInt(int $timestamp): self
    {
        return new self($timestamp);
    }

    public static function fromNullableInt(?int $timestamp): ?self
    {
        return null !== $timestamp ? new self($timestamp) : null;
    }

    public static function fromDateAtMidnight(string $date): self
    {
        $timestamp = strtotime($date.' 00:00:00');

        if (false === $timestamp) {
            throw new \InvalidArgumentException("No se puede parsear la fecha: {$date}");
        }

        return new self($timestamp);
    }

    public function toDateTime(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->setTimestamp($this->value);
    }

    public function toDateTimeWithTimezone(string $timezone = self::DEFAULT_TIMEZONE): \DateTimeImmutable
    {
        return $this->toDateTime()->setTimezone(new \DateTimeZone($timezone));
    }

    public function toCarbon(): Carbon
    {
        return Carbon::createFromTimestamp($this->value);
    }

    public function toCarbonWithTimezone(string $timezone = self::DEFAULT_TIMEZONE): Carbon
    {
        return Carbon::createFromTimestamp($this->value, $timezone);
    }

    public function format(string $format = self::DEFAULT_FORMAT, ?string $timezone = null): string
    {
        if (null !== $timezone) {
            return $this->toCarbonWithTimezone($timezone)->format($format);
        }

        return date($format, $this->value);
    }

    public function toDateString(?string $timezone = null): string
    {
        return $this->format(self::DATE_FORMAT, $timezone);
    }

    public function toTimeString(?string $timezone = null): string
    {
        return $this->format(self::TIME_FORMAT, $timezone);
    }

    public function toLocalFormat(string $format = self::DEFAULT_FORMAT): string
    {
        return $this->format($format, self::DEFAULT_TIMEZONE);
    }

    public function diffInSeconds(self $other): int
    {
        return abs($this->value - $other->value);
    }

    public function secondsSince(self $other): int
    {
        return $this->value - $other->value;
    }

    public function addSeconds(int $seconds): self
    {
        return new self($this->value + $seconds);
    }

    public function subSeconds(int $seconds): self
    {
        return new self($this->value - $seconds);
    }

    public function addDays(int $days): self
    {
        return $this->addSeconds($days * 86400);
    }

    public function isSameDay(self $other): bool
    {
        return $this->toDateString() === $other->toDateString();
    }

    public function isToday(): bool
    {
        return $this->toDateString() === date(self::DATE_FORMAT);
    }

    public function isBefore(self $other): bool
    {
        return $this->value < $other->value;
    }

    public function isAfter(self $other): bool
    {
        return $this->value > $other->value;
    }

    public function isBeforeOrEqual(self $other): bool
    {
        return $this->value <= $other->value;
    }

    public function isAfterOrEqual(self $other): bool
    {
        return $this->value >= $other->value;
    }

    public function startOfDay(): self
    {
        return self::fromDateAtMidnight($this->toDateString());
    }

    public function endOfDay(): self
    {
        $midnight = $this->startOfDay();

        return new self($midnight->value + 86399); // 23:59:59
    }

    public function diffInDays(self $other): int
    {
        return (int) floor(abs($this->value - $other->value) / 86400);
    }

    public function isPast(): bool
    {
        return $this->value < time();
    }

    public function isFuture(): bool
    {
        return $this->value > time();
    }
}
