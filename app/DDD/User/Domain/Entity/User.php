<?php

namespace App\DDD\User\Domain\Entity;

use App\DDD\Authentication\Domain\ValueObjects\HashedPassword;
use App\DDD\Shared\Domain\Event\RecordsDomainEvents;
use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
use App\DDD\TimeTracking\Domain\Exceptions\NoOpenTimeEntryException;
use App\DDD\TimeTracking\Domain\Exceptions\OpenTimeEntryAlreadyExistsException;
use App\DDD\TimeTracking\Domain\Exceptions\UnsavedUserCannotClockInException;
use App\DDD\TimeTracking\Domain\Interface\ClockInValidatorInterface;
use App\DDD\TimeTracking\Domain\Interface\ClockOutValidatorInterface;
use App\DDD\User\Domain\Event\UserActivationToggledEvent;
use App\DDD\User\Domain\Event\UserDeletedEvent;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\Name;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;

final class User
{
    use RecordsDomainEvents;

    private function __construct(
        private ?UserId $id,
        private Uuid $uuid,
        private Email $email,
        private Name $name,
        private bool $isActive = true,
        private ?HashedPassword $password = null,
        /** @var TimeEntry[] */
        private array $timeEntries = [],
        /** @var string[] */
        private array $roleSlugs = [],
    ) {
    }

    public static function create(Email $email, Name $name, HashedPassword $hashedPassword): self
    {
        return new self(null, Uuid::generate(), $email, $name, false, $hashedPassword);
    }

    public function password(): ?HashedPassword
    {
        return $this->password;
    }

    /**
     * @param array<array-key, mixed> $timeEntries
     * @param string[]                $roleSlugs
     */
    public static function fromPrimitives(
        ?int $id,
        string $uuid,
        string $email,
        string $name,
        bool $isActive,
        array $timeEntries = [],
        array $roleSlugs = [],
    ): self {
        $user = new self(
            null !== $id ? new UserId($id) : null,
            new Uuid($uuid),
            new Email($email),
            Name::make($name),
            $isActive,
            null,
            [],
            $roleSlugs
        );

        foreach ($timeEntries as $entry) {
            $user->addTimeEntry(TimeEntry::fromPrimitives(
                $entry['id'],
                $entry['user_id'],
                $entry['entrada'],
                $entry['salida'],
                (bool) ($entry['auto_closed'] ?? false),
                $entry['auto_close_reason'] ?? null
            ));
        }

        return $user;
    }

    public function id(): ?UserId
    {
        return $this->id;
    }

    public function uuid(): Uuid
    {
        return $this->uuid;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function toggleActive(): bool
    {
        $this->isActive = !$this->isActive;

        $this->recordEvent(new UserActivationToggledEvent(
            (string) ($this->id?->value() ?? $this->uuid->value()),
            $this->id->value(),
            $this->isActive,
        ));

        return $this->isActive;
    }

    public function hasRole(string $roleSlug): bool
    {
        return in_array($roleSlug, $this->roleSlugs, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * @throws UnauthorizedException
     */
    public function delete(UserRepositoryInterface $repository): void
    {
        if ($this->isSuperAdmin()) {
            throw UnauthorizedException::forDelete();
        }

        $repository->delete($this->id);

        $this->recordEvent(new UserDeletedEvent(
            (string) $this->id->value(),
            $this->id->value(),
            $this->email->value(),
        ));
    }

    /** @return string[] */
    public function roleSlugs(): array
    {
        return $this->roleSlugs;
    }

    public function ensureCanClockIn(ClockInValidatorInterface $validator): void
    {
        $validator->ensureCanClockIn($this);
    }

    public function ensureCanClockOut(ClockOutValidatorInterface $validator): void
    {
        $validator->ensureCanClockOut($this);
    }

    /** @return TimeEntry[] */
    public function timeEntries(): array
    {
        return $this->timeEntries;
    }

    public function addTimeEntry(TimeEntry $timeEntry): void
    {
        $this->timeEntries[] = $timeEntry;
    }

    public function clockIn(): void
    {
        if ($this->getOpenTimeEntry()) {
            throw new OpenTimeEntryAlreadyExistsException();
        }

        if (!$this->id()) {
            throw new UnsavedUserCannotClockInException();
        }

        $this->addTimeEntry(
            TimeEntry::create($this->id())
        );
    }

    public function clockOut(): void
    {
        $openEntry = $this->getOpenTimeEntry();
        if (!$openEntry) {
            throw new NoOpenTimeEntryException();
        }

        $openEntry->close();
    }

    private function getOpenTimeEntry(): ?TimeEntry
    {
        foreach ($this->timeEntries as $entry) {
            if ($entry->isOpen()) {
                return $entry;
            }
        }

        return null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ? $this->id->value() : null,
            'uuid' => $this->uuid->value(),
            'email' => $this->email->value(),
            'name' => $this->name->value(),
            'is_active' => $this->isActive(),
            'registros_horarios' => array_map(function (TimeEntry $entry) {
                return $entry->toArray();
            }, $this->timeEntries),
        ];
    }
}
