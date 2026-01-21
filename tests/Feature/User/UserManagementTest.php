<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    private User $authenticatedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Seed roles if not present
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Crear y autenticar un usuario admin para todos los tests
        $this->authenticatedUser = User::create([
            'uuid' => Str::orderedUuid(),
            'name' => 'Admin Test User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->authenticatedUser->assignRole('super_admin');

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
        // Crear un usuario usando el modelo Eloquent
        $testUser = User::create([
            'uuid' => Str::orderedUuid(),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->get("/user/{$testUser->id}");

        $response->assertStatus(200);
        $response->assertViewIs('users.show');
        $response->assertViewHas('user');
    }

    public function test_can_delete_user(): void
    {
        // Crear un usuario usando el modelo Eloquent
        $testUser = User::create([
            'uuid' => Str::orderedUuid(),
            'name' => 'Test User',
            'email' => 'delete-test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->delete("/user/{$testUser->id}");

        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'Usuario eliminado correctamente');

        // Verificar que el usuario fue eliminado
        $this->assertDatabaseMissing('users', [
            'id' => $testUser->id,
        ]);
    }

    public function test_can_toggle_user_active_status(): void
    {
        // Crear un usuario (is_active = false por defecto)
        $testUser = User::create([
            'uuid' => Str::orderedUuid(),
            'name' => 'Toggle User',
            'email' => 'toggle@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        // Verificar que está inactivo inicialmente
        $this->assertDatabaseHas('users', [
            'id' => $testUser->id,
            'is_active' => false,
        ]);

        // Activar usuario
        $response = $this->patch("/user/{$testUser->id}/toggle-active");
        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'Usuario activado correctamente');

        // Verificar que se activó
        $this->assertDatabaseHas('users', [
            'id' => $testUser->id,
            'is_active' => true,
        ]);

        // Desactivar usuario
        $response = $this->patch("/user/{$testUser->id}/toggle-active");
        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'Usuario desactivado correctamente');

        // Verificar que se desactivó
        $this->assertDatabaseHas('users', [
            'id' => $testUser->id,
            'is_active' => false,
        ]);
    }
}
