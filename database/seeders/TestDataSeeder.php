<?php

namespace Database\Seeders;

use App\Models\HolidayRequest;
use App\Models\Notification;
use App\Models\Role;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $superAdminRole = Role::where('slug', 'super_admin')->firstOrFail();
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $supervisorRole = Role::where('slug', 'supervisor')->firstOrFail();
        $employeeRole = Role::where('slug', 'employee')->firstOrFail();

        // --- Super Admin ---
        $admin = $this->createUser('Admin Principal', 'admin@fichaje.test', $superAdminRole);
        $this->createWorkHistory($admin, weeksBack: 8);

        // --- Admin ---
        $admin2 = $this->createUser('Laura García', 'laura@fichaje.test', $adminRole);
        $this->createWorkHistory($admin2, weeksBack: 6);

        // --- Supervisores ---
        $supervisor1 = $this->createUser('Carlos López', 'carlos@fichaje.test', $supervisorRole);
        $this->createWorkHistory($supervisor1, weeksBack: 10);

        $supervisor2 = $this->createUser('Ana Martínez', 'ana@fichaje.test', $supervisorRole);
        $this->createWorkHistory($supervisor2, weeksBack: 8);

        // --- Empleados activos ---
        $employees = [
            ['Pedro Sánchez', 'pedro@fichaje.test'],
            ['María Fernández', 'maria@fichaje.test'],
            ['Juan Rodríguez', 'juan@fichaje.test'],
            ['Elena Torres', 'elena@fichaje.test'],
            ['David Ruiz', 'david@fichaje.test'],
        ];

        $activeEmployees = [];
        foreach ($employees as [$name, $email]) {
            $user = $this->createUser($name, $email, $employeeRole);
            $this->createWorkHistory($user, weeksBack: fake()->numberBetween(4, 12));
            $activeEmployees[] = $user;
        }

        // --- Empleado con fichaje abierto (trabajando ahora) ---
        $working = $activeEmployees[0];
        TimeEntry::create([
            'user_id' => $working->id,
            'entrada' => time() - fake()->numberBetween(60 * 30, 3 * 3600),
            'salida' => null,
            'auto_closed' => false,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // --- Empleados inactivos ---
        $inactive1 = $this->createUser('Roberto Díaz', 'roberto@fichaje.test', $employeeRole, isActive: false);
        $this->createWorkHistory($inactive1, weeksBack: 2);

        $inactive2 = $this->createUser('Sofía Moreno', 'sofia@fichaje.test', $employeeRole, isActive: false);

        // --- Solicitudes de vacaciones ---
        $this->createHolidayRequests($activeEmployees, $supervisor1);

        // --- Notificaciones ---
        $this->createNotifications($activeEmployees, $admin);

        $this->command->info('Datos de prueba creados:');
        $this->command->table(
            ['Recurso', 'Cantidad'],
            [
                ['Usuarios activos', User::where('is_active', true)->count()],
                ['Usuarios inactivos', User::where('is_active', false)->count()],
                ['Fichajes', TimeEntry::count()],
                ['Fichajes abiertos', TimeEntry::whereNull('salida')->count()],
                ['Solicitudes vacaciones', HolidayRequest::count()],
                ['Notificaciones', Notification::count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('Credenciales: cualquier email con password "password"');
    }

    private function createUser(string $name, string $email, Role $role, bool $isActive = true): User
    {
        $now = time();
        $user = User::create([
            'uuid' => (string) Str::orderedUuid(),
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => $isActive,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $user->roles()->attach($role->id, [
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $user;
    }

    private function createWorkHistory(User $user, int $weeksBack): void
    {
        $startDate = now()->subWeeks($weeksBack)->startOfWeek();
        $today = now();

        $currentDay = $startDate->copy();

        while ($currentDay->lt($today)) {
            if ($currentDay->isWeekend()) {
                $currentDay->addDay();

                continue;
            }

            // Simular ausencias aleatorias (~10%)
            if (fake()->boolean(10)) {
                $currentDay->addDay();

                continue;
            }

            $entradaHour = fake()->numberBetween(7, 9);
            $entradaMinute = fake()->numberBetween(0, 59);
            $entrada = $currentDay->copy()->setTime($entradaHour, $entradaMinute)->getTimestamp();

            $workedHours = fake()->randomFloat(1, 7, 9.5);
            $salida = $entrada + (int) ($workedHours * 3600);

            // No crear fichajes futuros
            if ($entrada >= time()) {
                break;
            }

            $salidaFinal = min($salida, time() - 60);
            $autoClosed = fake()->boolean(5);

            TimeEntry::create([
                'user_id' => $user->id,
                'entrada' => $entrada,
                'salida' => $salidaFinal,
                'auto_closed' => $autoClosed,
                'auto_close_reason' => $autoClosed ? 'Cierre automático por fin de jornada' : null,
                'created_at' => $entrada,
                'updated_at' => $salidaFinal,
            ]);

            $currentDay->addDay();
        }
    }

    private function createHolidayRequests(array $employees, User $supervisor): void
    {
        $now = time();

        // Solicitudes pendientes
        foreach (array_slice($employees, 0, 2) as $employee) {
            HolidayRequest::create([
                'user_id' => $employee->id,
                'start_date' => now()->addWeeks(fake()->numberBetween(2, 6))->startOfWeek()->getTimestamp(),
                'end_date' => now()->addWeeks(fake()->numberBetween(7, 10))->endOfWeek()->getTimestamp(),
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Solicitud aprobada
        HolidayRequest::create([
            'user_id' => $employees[2]->id,
            'start_date' => now()->addMonth()->startOfWeek()->getTimestamp(),
            'end_date' => now()->addMonth()->addWeek()->endOfWeek()->getTimestamp(),
            'status' => 'approved',
            'created_at' => $now - 86400 * 5,
            'updated_at' => $now - 86400 * 2,
        ]);

        // Solicitud rechazada
        HolidayRequest::create([
            'user_id' => $employees[3]->id,
            'start_date' => now()->addWeeks(3)->startOfWeek()->getTimestamp(),
            'end_date' => now()->addWeeks(4)->endOfWeek()->getTimestamp(),
            'status' => 'rejected',
            'created_at' => $now - 86400 * 7,
            'updated_at' => $now - 86400 * 3,
        ]);
    }

    private function createNotifications(array $employees, User $admin): void
    {
        $now = time();

        // Notificaciones no leídas
        Notification::create([
            'user_id' => $admin->id,
            'type' => 'holiday_request',
            'title' => 'Nueva solicitud de vacaciones',
            'message' => $employees[0]->name.' ha solicitado vacaciones.',
            'data' => json_encode(['user_id' => $employees[0]->id]),
            'read_at' => null,
            'created_at' => $now - 3600,
            'updated_at' => $now - 3600,
        ]);

        Notification::create([
            'user_id' => $admin->id,
            'type' => 'holiday_request',
            'title' => 'Nueva solicitud de vacaciones',
            'message' => $employees[1]->name.' ha solicitado vacaciones.',
            'data' => json_encode(['user_id' => $employees[1]->id]),
            'read_at' => null,
            'created_at' => $now - 1800,
            'updated_at' => $now - 1800,
        ]);

        // Notificación leída
        Notification::create([
            'user_id' => $employees[2]->id,
            'type' => 'holiday_approved',
            'title' => 'Vacaciones aprobadas',
            'message' => 'Tu solicitud de vacaciones ha sido aprobada.',
            'data' => json_encode([]),
            'read_at' => $now - 7200,
            'created_at' => $now - 86400,
            'updated_at' => $now - 7200,
        ]);

        // Notificación de sistema
        Notification::create([
            'user_id' => $employees[3]->id,
            'type' => 'holiday_rejected',
            'title' => 'Vacaciones rechazadas',
            'message' => 'Tu solicitud de vacaciones ha sido rechazada.',
            'data' => json_encode([]),
            'read_at' => null,
            'created_at' => $now - 86400 * 2,
            'updated_at' => $now - 86400 * 2,
        ]);
    }
}
