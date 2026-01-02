<?php

namespace App\DDD\Authentication\Domain\Services;

use App\DDD\Authentication\Domain\ValueObjects\HashedPassword;
use App\DDD\Authentication\Domain\ValueObjects\PlainPassword;

interface PasswordHashingService
{
    public function hash(PlainPassword $password): HashedPassword;

    public function verify(PlainPassword $plainPassword, HashedPassword $hashedPassword): bool;
}
