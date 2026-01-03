<?php

namespace Tests\Feature\User;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private \App\Models\User $authenticatedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Crear y autenticar un usuario admin para todos los tests
        $this->authenticatedUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Admin Test User',
            'email' => 'admin@test.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
            'remember_token' => 'soyAdm1n', // Admin user
        ]);

        $this->actingAs($this->authenticatedUser);
    }

    public function test_can_view_users_index_page(): void
    {
        $response = $this->get('/user');

        $response->assertStatus(200);
        $response->assertViewIs('users.index');
        $response->assertViewHas('users');
    }

    public function test_can_view_specific_user(): void
    {
        // Crear un usuario primero
        $repository = new EloquentUserRepository;
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $repository->save($user);

        $response = $this->get("/user/{$savedUser->id()->getValue()}");

        $response->assertStatus(200);
        $response->assertViewIs('users.show');
        $response->assertViewHas('user');
    }

    public function test_can_delete_user(): void
    {
        // Crear un usuario primero
        $repository = new EloquentUserRepository;
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $repository->save($user);

        $response = $this->delete("/user/{$savedUser->id()->getValue()}");

        $response->assertRedirect('/user');
        $response->assertSessionHas('success', 'Usuario eliminado correctamente');

        // Verificar que el usuario fue eliminado
        $this->assertDatabaseMissing('users', [
            'id' => $savedUser->id()->getValue(),
        ]);
    }

    public function test_can_get_users_as_json(): void
    {
        // Crear algunos usuarios
        $repository = new EloquentUserRepository;
        $user1 = User::create(new Email('user1@example.com'), 'User One');
        $user2 = User::create(new Email('user2@example.com'), 'User Two');
        $repository->save($user1);
        $repository->save($user2);

        $response = $this->getJson('/user');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'uuid',
                'email',
                'name',
                'is_active',
                'tiempo_acumulado',
            ],
        ]);
    }

    public function test_can_toggle_user_active_status(): void
    {
        // Crear un usuario
        $repository = new EloquentUserRepository;
        $user = User::create(new Email('toggle@example.com'), 'Toggle User');
        $savedUser = $repository->save($user);

        // Verificar que está activo inicialmente
        $this->assertDatabaseHas('users', [
            'id' => $savedUser->id()->getValue(),
            'is_active' => true,
        ]);

        // Desactivar el usuario
        $response = $this->patch("/user/{$savedUser->id()->getValue()}/toggle-active");
        $response->assertRedirect('/user');
        $response->assertSessionHas('success', 'Usuario desactivado correctamente');

        // Verificar que se desactivó
        $this->assertDatabaseHas('users', [
            'id' => $savedUser->id()->getValue(),
            'is_active' => false,
        ]);

        // Activar el usuario nuevamente
        $response = $this->patch("/user/{$savedUser->id()->getValue()}/toggle-active");
        $response->assertRedirect('/user');
        $response->assertSessionHas('success', 'Usuario activado correctamente');

        // Verificar que se activó
        $this->assertDatabaseHas('users', [
            'id' => $savedUser->id()->getValue(),
            'is_active' => true,
        ]);
    }
}
