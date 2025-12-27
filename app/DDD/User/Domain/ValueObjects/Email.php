<?php
namespace App\DDD\User\Domain\ValueObjects;
use InvalidArgumentException;
final class Email {
    private string $value;
    public function __construct(string $value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
        $this->value = strtolower(trim($value));
    }
    public function getValue(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}