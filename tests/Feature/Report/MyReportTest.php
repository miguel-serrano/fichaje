<?php

namespace Tests\Feature\Report;

use App\Models\HolidayRequest;
use App\Models\TimeEntry;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class MyReportTest extends TestCase
{
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $this->employee = User::factory()->create(['is_active' => true]);
        $this->employee->assignRole('employee');
    }

    public function testReportShowsHoursWorkedThisMonthFromClosedEntriesOnly(): void
    {
        $monthStart = strtotime(date('Y-m-01 00:00:00'));
        $this->createEntry($monthStart + 3600, 2 * 3600);
        $this->createEntry($monthStart + 5 * 3600, 90 * 60);
        $this->createEntry($monthStart - 86400, 8 * 3600);
        TimeEntry::factory()->open()->create(['user_id' => $this->employee->id]);

        $response = $this->actingAs($this->employee)->get(route('report.me'));

        $response->assertOk();
        $response->assertViewHas('hoursWorked', 3.5);
    }

    public function testReportCountsOnlyApprovedHolidayDaysThatFallInTheCurrentYear(): void
    {
        $year = (int) date('Y');
        $this->createHoliday("{$year}-02-10", "{$year}-02-12", 'approved');
        $this->createHoliday(($year - 1).'-12-28', "{$year}-01-03", 'approved');
        $this->createHoliday("{$year}-12-30", ($year + 1).'-01-02', 'approved');
        $this->createHoliday("{$year}-06-01", "{$year}-06-05", 'pending');
        $this->createHoliday("{$year}-07-01", "{$year}-07-05", 'rejected');

        $response = $this->actingAs($this->employee)->get(route('report.me'));

        $response->assertOk();
        $response->assertViewHas('approvedDays', 3 + 3 + 2);
    }

    public function testReportTargetsComeFromConfiguration(): void
    {
        config(['users.report.monthly_hours_target' => 120, 'users.report.yearly_holidays_target' => 25]);

        $response = $this->actingAs($this->employee)->get(route('report.me'));

        $response->assertViewHas('hoursTarget', 120);
        $response->assertViewHas('holidaysTarget', 25);
    }

    private function createEntry(int $entrada, int $durationSeconds): void
    {
        TimeEntry::factory()->create([
            'user_id' => $this->employee->id,
            'entrada' => $entrada,
            'salida' => $entrada + $durationSeconds,
        ]);
    }

    private function createHoliday(string $startDate, string $endDate, string $status): void
    {
        HolidayRequest::factory()->create([
            'user_id' => $this->employee->id,
            'start_date' => strtotime("{$startDate} 00:00:00"),
            'end_date' => strtotime("{$endDate} 00:00:00"),
            'status' => $status,
        ]);
    }
}
