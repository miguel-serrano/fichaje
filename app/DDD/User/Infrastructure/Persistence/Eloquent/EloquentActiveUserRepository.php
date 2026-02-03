<?php

declare(strict_types=1);

namespace App\DDD\User\Infrastructure\Persistence\Eloquent;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UserNotActiveException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\ActiveUserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Uuid;

class EloquentActiveUserRepository extends EloquentUserRepository implements ActiveUserRepositoryInterface
{
    public function findActiveByUuidOrFail(Uuid $uuid): User
    {
        $row = $this->query()->where('uuid', $uuid->value())->first();

        if (!$row) {
            throw UserNotFoundException::byUuid($uuid);
        }

        if (!$row->is_active) {
            throw UserNotActiveException::forUuid($uuid);
        }

        $timeEntries = $this->getTimeEntriesForUser((int) $row->id);

        $roleSlugs = $this->getRoleSlugsForUser((int) $row->id);

        return $this->toDomainEntity($row, $timeEntries, $roleSlugs);
    }
}
