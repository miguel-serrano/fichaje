<?php

namespace Tests\Feature\Integration;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\Role;
use App\Models\User as EloquentUser;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserRegistroHorarioIntegrationTest extends TestCase
{
    private UserRepositoryInterface $userRepository;

    private EloquentUser $authenticatedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Ejecutar seeders de roles y permisos
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $this->userRepository = $this->app->make(UserRepositoryInterface::class);
        $this->authenticatedUser = EloquentUser::create([
            'uuid' => Str::orderedUuid(),
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'is_admin' => true,
        ]);

        // Asignar rol super_admin
        $superAdminRole = Role::where('slug', 'super_admin')->first();
        if ($superAdminRole) {
            $this->authenticatedUser->roles()->attach($superAdminRole->id);
        }

        $this->actingAs($this->authenticatedUser);
    }

    public function test_users_index_shows_user_list(): void
    {
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $this->userRepository->save($user);

        $response = $this->get('/users');

        $response->assertStatus(200);
        $response->assertViewIs('users.index');

        $users = $response->viewData('users');
        $this->assertNotEmpty($users);

        $testUser = collect($users)->firstWhere('email', 'test@example.com');
        $this->assertNotNull($testUser);
        $this->assertEquals('test@example.com', $testUser['email']);
        $this->assertEquals('Test User', $testUser['name']);
    }

    public function test_complete_workflow_registro_horario(): void
    {
        $entradaResponse = $this->post('/registro-horario/entrada');
        $entradaResponse->assertRedirect(route('user.me'));
        $entradaResponse->assertSessionHas('success');

        $registroResponse = $this->get('/registro-horario');
        $registroResponse->assertStatus(200);

        $tieneRegistroAbierto = $registroResponse->viewData('tieneRegistroAbierto');
        $this->assertTrue($tieneRegistroAbierto);

        sleep(1);

        $salidaResponse = $this->post('/registro-horario/salida');
        $salidaResponse->assertRedirect(route('user.me'));
        $salidaResponse->assertSessionHas('success');

        $finalResponse = $this->get('/registro-horario');
        $finalResponse->assertStatus(200);

        $tieneRegistroAbiertoFinal = $finalResponse->viewData('tieneRegistroAbierto');
        $this->assertFalse($tieneRegistroAbiertoFinal);
    }

    public function test_user_deletion_handles_registro_horario_gracefully(): void
    {
        $user = User::create(new Email('delete@example.com'), 'Delete User');
        $savedUser = $this->userRepository->save($user);

        $userWithEntries = $this->userRepository->findById(new UserId($savedUser->id()->value()));
        $userWithEntries->clockIn();
        $this->userRepository->save($userWithEntries);
        $userWithEntries->clockOut();
        $this->userRepository->save($userWithEntries);

        $deleteResponse = $this->delete("/user/{$savedUser->id()->value()}");
        $deleteResponse->assertRedirect('/users');
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $savedUser->id()->value(),
        ]);

        $this->assertDatabaseMissing('registro_horarios', [
            'user_id' => $savedUser->id()->value(),
        ]);

        $usersResponse = $this->get('/users');
        $usersResponse->assertStatus(200);
    }
}
