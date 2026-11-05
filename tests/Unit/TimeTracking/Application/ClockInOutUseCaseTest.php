<?php

namespace Tests\Unit\TimeTracking\Application;

use App\DDD\Authorization\Domain\Exceptions\AccessDeniedException;
use App\DDD\TimeTracking\Application\Command\ClockInCommand;
use App\DDD\TimeTracking\Application\Command\ClockOutCommand;
use App\DDD\TimeTracking\Application\Handler\ClockInCommandHandler;
use App\DDD\TimeTracking\Application\Handler\ClockOutCommandHandler;
use App\DDD\TimeTracking\Domain\Event\UserClockedInEvent;
use App\DDD\TimeTracking\Domain\Event\UserClockedOutEvent;
use App\DDD\TimeTracking\Domain\Exceptions\DailyTimeEntryLimitExceededException;
use App\DDD\TimeTracking\Domain\Exceptions\NoOpenTimeEntryException;
use App\DDD\TimeTracking\Domain\Exceptions\OpenTimeEntryAlreadyExistsException;
use App\DDD\TimeTracking\Domain\Services\DailyLimitValidatorService;
use App\DDD\User\Domain\Exceptions\UserNotActiveException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\Mail\DomainEventMail;
use App\Models\TimeEntry;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClockInOutUseCaseTest extends TestCase
{
    private ClockInCommandHandler $clockInHandler;

    private ClockOutCommandHandler $clockOutHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        Mail::fake();

        $this->clockInHandler = $this->app->make(ClockInCommandHandler::class);
        $this->clockOutHandler = $this->app->make(ClockOutCommandHandler::class);
    }

    public function testEmployeeClocksIn(): void
    {
        $this->createUserWithRole('super_admin');
        $employee = $this->createUserWithRole('employee');

        $this->clockInHandler->handle(ClockInCommand::create($employee->uuid));

        $this->assertDatabaseHas('time_entries', ['user_id' => $employee->id, 'salida' => null]);
        Mail::assertQueued(DomainEventMail::class, fn (DomainEventMail $mail): bool => $mail->eventName === UserClockedInEvent::eventName());
    }

    public function testClockInFailsWhenUserDoesNotExist(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->clockInHandler->handle(ClockInCommand::create(Str::uuid()->toString()));
    }

    public function testClockInFailsWhenUserIsNotActive(): void
    {
        $inactive = $this->createUserWithRole('employee', isActive: false);

        $this->expectException(UserNotActiveException::class);

        $this->clockInHandler->handle(ClockInCommand::create($inactive->uuid));
    }

    public function testClockInFailsWithoutPermission(): void
    {
        $userWithoutRole = User::factory()->create(['is_active' => true]);

        try {
            $this->clockInHandler->handle(ClockInCommand::create($userWithoutRole->uuid));
            $this->fail('Expected AccessDeniedException was not thrown');
        } catch (AccessDeniedException) {
            $this->assertDatabaseMissing('time_entries', ['user_id' => $userWithoutRole->id]);
        }
    }

    public function testClockInFailsWhenAnEntryIsAlreadyOpen(): void
    {
        $employee = $this->createUserWithRole('employee');
        TimeEntry::factory()->open()->create(['user_id' => $employee->id]);

        $this->expectException(OpenTimeEntryAlreadyExistsException::class);

        $this->clockInHandler->handle(ClockInCommand::create($employee->uuid));
    }

    public function testClockInFailsWhenDailyLimitIsReached(): void
    {
        $employee = $this->createUserWithRole('employee');
        $this->createClosedEntriesToday($employee, DailyLimitValidatorService::MAX_DAILY_ENTRIES);

        $this->expectException(DailyTimeEntryLimitExceededException::class);

        $this->clockInHandler->handle(ClockInCommand::create($employee->uuid));
    }

    public function testSuperAdminIsNotLimitedByDailyEntries(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');
        $this->createClosedEntriesToday($superAdmin, DailyLimitValidatorService::MAX_DAILY_ENTRIES);

        $this->clockInHandler->handle(ClockInCommand::create($superAdmin->uuid));

        $this->assertDatabaseHas('time_entries', ['user_id' => $superAdmin->id, 'salida' => null]);
    }

    public function testEmployeeClocksOut(): void
    {
        $this->createUserWithRole('super_admin');
        $employee = $this->createUserWithRole('employee');
        $openEntry = TimeEntry::factory()->open()->create(['user_id' => $employee->id]);

        $this->clockOutHandler->handle(ClockOutCommand::create($employee->uuid));

        $this->assertNotNull($openEntry->fresh()->salida);
        Mail::assertQueued(DomainEventMail::class, fn (DomainEventMail $mail): bool => $mail->eventName === UserClockedOutEvent::eventName());
    }

    public function testClockOutFailsWithoutAnOpenEntry(): void
    {
        $employee = $this->createUserWithRole('employee');
        TimeEntry::factory()->create(['user_id' => $employee->id]);

        $this->expectException(NoOpenTimeEntryException::class);

        $this->clockOutHandler->handle(ClockOutCommand::create($employee->uuid));
    }

    private function createUserWithRole(string $roleSlug, bool $isActive = true): User
    {
        $user = User::factory()->create(['is_active' => $isActive]);
        $user->assignRole($roleSlug);

        return $user;
    }

    private function createClosedEntriesToday(User $user, int $count): void
    {
        $startOfDay = strtotime('today');

        for ($index = 0; $index < $count; ++$index) {
            $entrada = $startOfDay + ($index * 60);

            TimeEntry::factory()->create([
                'user_id' => $user->id,
                'entrada' => $entrada,
                'salida' => $entrada + 30,
            ]);
        }
    }
}
