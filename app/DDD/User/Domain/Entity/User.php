<?php

namespace App\DDD\User\Domain\Entity;

use App\DDD\TimeTracking\Domain\Exceptions\NoOpenTimeEntryException;
use App\DDD\TimeTracking\Domain\Exceptions\OpenTimeEntryAlreadyExistsException;
use App\DDD\TimeTracking\Domain\Exceptions\UnsavedUserCannotClockInException;
use App\DDD\TimeTracking\Domain\TimeEntry;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;

final class User
{
    private ?UserId $id;

    private Uuid $uuid;

    private Email $email;

    private string $name;

    private bool $isActive;

    /** @var TimeEntry[] */
    private array $timeEntries;

    private function __construct(
        ?UserId $id,
        Uuid $uuid,
        Email $email,
        string $name,
        bool $isActive = true,
        array $timeEntries = [],
    ) {
        $this->id = $id;
        $this->uuid = $uuid;
        $this->email = $email;
        $this->name = $name;
        $this->isActive = $isActive;
        $this->timeEntries = $timeEntries;
    }

    public static function create(Email $email, string $name): self
    {
        return new self(null, Uuid::generate(), $email, $name, false);
    }

    public static function fromPrimitives(
        ?int $id,
        string $uuid,
        string $email,
        string $name,
        bool $isActive,
        array $timeEntries = [],
    ): self {
        $user = new self(
            null !== $id ? new UserId($id) : null,
            new Uuid($uuid),
            new Email($email),
            $name,
            $isActive
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

    public function name(): string
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

        return $this->isActive;
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
            'name' => $this->name(),
            'is_active' => $this->isActive(),
            'registros_horarios' => array_map(function (TimeEntry $entry) {
                return $entry->toArray();
            }, $this->timeEntries),
        ];
    }
}
