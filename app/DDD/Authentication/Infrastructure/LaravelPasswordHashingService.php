<?php

namespace App\DDD\Authentication\Infrastructure;

use App\DDD\Authentication\Domain\Services\PasswordHashingService;
use App\DDD\Authentication\Domain\ValueObjects\HashedPassword;
use App\DDD\Authentication\Domain\ValueObjects\PlainPassword;
use Illuminate\Support\Facades\Hash;

final class LaravelPasswordHashingService implements PasswordHashingService
{
    public function hash(PlainPassword $password): HashedPassword
    {
        return new HashedPassword(Hash::make($password->getValue()));
    }

    public function verify(PlainPassword $plainPassword, HashedPassword $hashedPassword): bool
    {
        return Hash::check($plainPassword->getValue(), $hashedPassword->getValue());
    }
}
