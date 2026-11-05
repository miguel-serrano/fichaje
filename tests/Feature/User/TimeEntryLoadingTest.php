<?php

namespace Tests\Feature\User;

use App\DDD\TimeTracking\Domain\Entity\TimeEntry as DomainTimeEntry;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\User\Domain\Interface\ActiveUserRepositoryInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;
use App\Models\HolidayRequest;
use App\Models\TimeEntry;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Leer un usuario no debe arrastrar todo su historial de fichajes:
 * cada caso de uso carga solo los fichajes que necesita.
 */
class TimeEntryLoadingTest extends TestCase
{
    private UserRepositoryInterface $userRepository;

    private TimeEntryRepositoryInterface $timeEntryRepository;

    private User $employee;

    /** @var array<string, int> */
    private array $entryIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        Mail::fake();

        $this->userRepository = $this->app->make(UserRepositoryInterface::class);
        $this->timeEntryRepository = $this->app->make(TimeEntryRepositoryInterface::class);

        $this->employee = User::factory()->create(['is_active' => true]);
        $this->employee->assignRole('employee');
        $this->createHistory($this->employee);
    }

    public function testFindByIdReturnsRolesButNoTimeEntries(): void
    {
        $user = $this->userRepository->findById(new UserId($this->employee->id));

        $this->assertSame([], $user->timeEntries());
        $this->assertSame(['employee'], $user->roleSlugs());
    }

    public function testFindByIdWithTimeEntriesReturnsTheWholeHistory(): void
    {
        $user = $this->userRepository->findByIdWithTimeEntriesOrFail(new UserId($this->employee->id));

        $this->assertEqualsCanonicalizing(array_values($this->entryIds), $this->idsOf($user->timeEntries()));
    }

    public function testFindByIdsLoadsSeveralUsersKeyedByIdAndIgnoresMissingOnes(): void
    {
        $other = User::factory()->create();

        $users = $this->userRepository->findByIds([
            new UserId($this->employee->id),
            new UserId($other->id),
            new UserId($this->employee->id),
            new UserId(999999),
        ]);

        $this->assertEqualsCanonicalizing([$this->employee->id, $other->id], array_keys($users));
        $this->assertSame([], $users[$this->employee->id]->timeEntries());
        $this->assertSame([], $this->userRepository->findByIds([]));
    }

    public function testActiveUserForClockingLoadsOnlyOpenAndTodayEntries(): void
    {
        $repository = $this->app->make(ActiveUserRepositoryInterface::class);

        $user = $repository->findActiveByUuidOrFail(new Uuid($this->employee->uuid));

        $this->assertEqualsCanonicalizing(
            [$this->entryIds['openFromYesterday'], $this->entryIds['closedToday']],
            $this->idsOf($user->timeEntries()),
        );
    }

    public function testTimeEntryRepositoryClockingWindowMatchesTheActiveUserWindow(): void
    {
        $entries = $this->timeEntryRepository->findClockingWindowByUserId(new UserId($this->employee->id));

        $this->assertEqualsCanonicalizing(
            [$this->entryIds['openFromYesterday'], $this->entryIds['closedToday']],
            $this->idsOf($entries),
        );
    }

    public function testFindByUserIdBetweenUsesAHalfOpenInterval(): void
    {
        $from = strtotime('today 00:00:00');
        $to = strtotime('tomorrow 00:00:00');
        $startingAtTomorrow = TimeEntry::factory()->create(['user_id' => $this->employee->id, 'entrada' => $to, 'salida' => $to + 60]);

        $entries = $this->timeEntryRepository->findByUserIdBetween(new UserId($this->employee->id), $from, $to);

        $this->assertSame([$this->entryIds['closedToday']], $this->idsOf($entries));
        $this->assertNotContains($startingAtTomorrow->id, $this->idsOf($entries));
    }

    public function testMyTimeEntriesPageShowsOnlyThisMonthEntries(): void
    {
        $response = $this->actingAs($this->employee)->get(route('user.me'));

        $response->assertOk();
        $monthIds = $this->idsOf($response->viewData('monthlyRegistros'));
        $this->assertContains($this->entryIds['closedToday'], $monthIds);
        $this->assertNotContains($this->entryIds['closedLastYear'], $monthIds);
    }

    public function testCommonPagesNeverLoadTheWholeHistory(): void
    {
        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdmin->assignRole('super_admin');

        $pages = [
            [$this->employee, route('registro_horario.index')],
            [$this->employee, route('user.me')],
            [$this->employee, route('report.me')],
            [$superAdmin, route('users.index')],
            [$superAdmin, route('admin.holidays.index')],
        ];

        foreach ($pages as [$user, $url]) {
            $fullHistoryQueries = $this->fullHistoryQueriesDuring(fn () => $this->actingAs($user)->get($url)->assertOk());

            $this->assertSame([], $fullHistoryQueries, "{$url} carga el historial completo de fichajes");
        }
    }

    public function testHolidayReviewPageLoadsUsersInASingleQuery(): void
    {
        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdmin->assignRole('super_admin');
        $this->actingAs($superAdmin);

        HolidayRequest::factory()->count(2)->create();
        $queriesWithTwoRequests = $this->countQueriesDuring(fn () => $this->get(route('admin.holidays.index'))->assertOk());

        HolidayRequest::factory()->count(4)->create();
        $queriesWithSixRequests = $this->countQueriesDuring(fn () => $this->get(route('admin.holidays.index'))->assertOk());

        $this->assertSame($queriesWithTwoRequests, $queriesWithSixRequests);
    }

    private function createHistory(User $user): void
    {
        $yesterday = strtotime('yesterday 09:00:00');
        $today = strtotime('today 00:00:00') + 60;
        $lastYear = strtotime('-1 year');

        $this->entryIds = [
            'closedLastYear' => TimeEntry::factory()->create(['user_id' => $user->id, 'entrada' => $lastYear, 'salida' => $lastYear + 3600])->id,
            'closedYesterday' => TimeEntry::factory()->create(['user_id' => $user->id, 'entrada' => $yesterday - 7200, 'salida' => $yesterday - 3600])->id,
            'openFromYesterday' => TimeEntry::factory()->create(['user_id' => $user->id, 'entrada' => $yesterday, 'salida' => null])->id,
            'closedToday' => TimeEntry::factory()->create(['user_id' => $user->id, 'entrada' => $today, 'salida' => $today + 30])->id,
        ];
    }

    /**
     * @param iterable<DomainTimeEntry> $entries
     *
     * @return int[]
     */
    private function idsOf(iterable $entries): array
    {
        $ids = [];
        foreach ($entries as $entry) {
            $ids[] = $entry->id()->value();
        }

        return $ids;
    }

    /**
     * Consultas a time_entries que solo filtran por usuario (sin acotar por fecha ni estado).
     *
     * @return string[]
     */
    private function fullHistoryQueriesDuring(callable $callback): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $callback();
        $queries = array_column(DB::getQueryLog(), 'query');
        DB::disableQueryLog();

        return array_values(array_filter(
            $queries,
            fn (string $sql): bool => str_starts_with(strtolower($sql), 'select')
                && str_contains($sql, '`time_entries`')
                && !str_contains($sql, 'entrada')
                && !str_contains($sql, 'salida'),
        ));
    }

    private function countQueriesDuring(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $callback();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }
}
