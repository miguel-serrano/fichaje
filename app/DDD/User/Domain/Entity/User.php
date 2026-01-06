<?php

namespace App\DDD\User\Domain\Entity;

use App\DDD\TimeTracking\Domain\Exceptions\DailyTimeEntryLimitExceededException;
use App\DDD\TimeTracking\Domain\Exceptions\NoOpenTimeEntryException;
use App\DDD\TimeTracking\Domain\Exceptions\OpenTimeEntryAlreadyExistsException;
use App\DDD\TimeTracking\Domain\Exceptions\UnsavedUserCannotClockInException;
use App\DDD\TimeTracking\Domain\TimeEntry;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;
use Carbon\Carbon;

final class User
{
    private ?UserId $id;

    private Uuid $uuid;

    private Email $email;

    private string $name;

    private bool $isActive;

    private bool $isAdmin;

    /** @var TimeEntry[] */
    private array $timeEntries;

    private function __construct(
        ?UserId $id,
        Uuid $uuid,
        Email $email,
        string $name,
        bool $isActive = true,
        bool $isAdmin = false,
        array $timeEntries = []
    ) {
        $this->id = $id;
        $this->uuid = $uuid;
        $this->email = $email;
        $this->name = $name;
        $this->isActive = $isActive;
        $this->isAdmin = $isAdmin;
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
        bool $isAdmin = false,
        array $timeEntries = []
    ): self {
        $user = new self(
            $id !== null ? new UserId($id) : null,
            new Uuid($uuid),
            new Email($email),
            $name,
            $isActive,
            $isAdmin
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

    public function isAdmin(): bool
    {
        return $this->isAdmin;
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
        $this->isActive = ! $this->isActive;

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
            throw new OpenTimeEntryAlreadyExistsException;
        }

        if (! $this->id()) {
            throw new UnsavedUserCannotClockInException;
        }

        // Validate daily limit (only for non-admin users)
        if (! $this->isAdmin()) {
            $todayEntries = $this->countTodayEntries();
            if ($todayEntries >= DailyTimeEntryLimitExceededException::MAX_DAILY_ENTRIES) {
                throw new DailyTimeEntryLimitExceededException($todayEntries);
            }
        }

        $this->addTimeEntry(
            TimeEntry::create($this->id())
        );
    }

    private function countTodayEntries(): int
    {
        $today = Carbon::today();
        $count = 0;

        foreach ($this->timeEntries as $entry) {
            $startTimeCarbon = Carbon::instance($entry->startTime());
            if ($startTimeCarbon->isSameDay($today)) {
                $count++;
            }
        }

        return $count;
    }

    public function clockOut(): void
    {
        $openEntry = $this->getOpenTimeEntry();
        if (! $openEntry) {
            throw new NoOpenTimeEntryException;
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
            'is_admin' => $this->isAdmin(),
            'registros_horarios' => array_map(function (TimeEntry $entry) {
                return $entry->toArray();
            }, $this->timeEntries),
        ];
    }
}
