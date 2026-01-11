<?php

namespace Tests\Feature\User;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use DatabaseTransactions;

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
            'is_admin' => true,
        ]);

        $this->actingAs($this->authenticatedUser);
    }

    public function test_can_view_users_index_page(): void
    {
        $response = $this->get('/users');

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

        $response = $this->get("/user/{$savedUser->id()->value()}");

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

        $response = $this->delete("/user/{$savedUser->id()->value()}");

        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'Usuario eliminado correctamente');

        // Verificar que el usuario fue eliminado
        $this->assertDatabaseMissing('users', [
            'id' => $savedUser->id()->value(),
        ]);
    }

    public function test_can_toggle_user_active_status(): void
    {
        // Crear un usuario (is_active = false por defecto)
        $repository = new EloquentUserRepository;
        $user = User::create(new Email('toggle@example.com'), 'Toggle User');
        $savedUser = $repository->save($user);

        // Verificar que está inactivo inicialmente (nuevo comportamiento)
        $this->assertDatabaseHas('users', [
            'id' => $savedUser->id()->value(),
            'is_active' => false,
        ]);

        // Activar usuario
        $response = $this->patch("/user/{$savedUser->id()->value()}/toggle-active");
        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'Usuario activado correctamente');

        // Verificar que se activó
        $this->assertDatabaseHas('users', [
            'id' => $savedUser->id()->value(),
            'is_active' => true,
        ]);

        // Desactivar usuario
        $response = $this->patch("/user/{$savedUser->id()->value()}/toggle-active");
        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'Usuario desactivado correctamente');

        // Verificar que se desactivó
        $this->assertDatabaseHas('users', [
            'id' => $savedUser->id()->value(),
            'is_active' => false,
        ]);
    }
}
