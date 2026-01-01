<?php

namespace Tests\Feature\RegistroHorario;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\ValueObjects\Uuid;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Carbon\Carbon;
use Exception;

class RegistroHorarioTest extends TestCase
{
    use RefreshDatabase;

    private UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        // Resolve the UserRepositoryInterface from the service container
        $this->userRepository = $this->app->make(UserRepositoryInterface::class);
    }

    public function test_can_view_registro_horario_index(): void
    {
        $response = $this->get('/registro-horario');
        
        $response->assertStatus(200);
        $response->assertViewIs('registro_horario');
        $response->assertViewHas(['users', 'segundos', 'selectedUserUuid', 'tieneRegistroAbierto']);
    }

    public function test_can_fichar_entrada(): void
    {
        // Crear un usuario primero
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $this->userRepository->save($user); // Save first to get ID

        $response = $this->post('/registro-horario/entrada', [
            'userUuid' => $savedUser->uuid()->getValue()
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Entrada registrada correctamente');
        
        // Verificar que se creó el registro
        $this->assertDatabaseHas('registro_horarios', [
            'user_id' => $savedUser->id()->getValue(), // Use the actual integer ID
            'salida' => null
        ]);
    }

    public function test_cannot_fichar_entrada_if_already_open(): void
    {
        // Crear un usuario y fichar entrada
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $this->userRepository->save($user); // Save first to get ID
        $userWithId = $this->userRepository->findById(new UserId($savedUser->id()->getValue()));
        $userWithId->ficharEntrada();
        $this->userRepository->save($userWithId); // Save the updated aggregate

        // Intentar fichar entrada de nuevo
        $response = $this->post('/registro-horario/entrada', [
            'userUuid' => $savedUser->uuid()->getValue()
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Ya existe un registro de entrada abierto.');
    }


    public function test_can_fichar_salida(): void
    {
        // Crear un usuario y fichar entrada
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $this->userRepository->save($user); // Save first to get ID
        $userWithOpenEntry = $this->userRepository->findById(new UserId($savedUser->id()->getValue()));
        $userWithOpenEntry->ficharEntrada();
        $this->userRepository->save($userWithOpenEntry); // Save the updated aggregate

        // Fichar salida
        $response = $this->post('/registro-horario/salida', [
            'userUuid' => $savedUser->uuid()->getValue()
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Salida registrada correctamente');
        
        // Verificar que se actualizó el registro con salida
        $this->assertDatabaseMissing('registro_horarios', [
            'user_id' => $savedUser->id()->getValue(),
            'salida' => null
        ]);
        $this->assertDatabaseHas('registro_horarios', [
            'user_id' => $savedUser->id()->getValue(),
            ['salida', '!=', null] // Assert that salida is not null
        ]);
    }

    public function test_cannot_fichar_salida_without_entrada(): void
    {
        // Crear un usuario sin fichar entrada
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $this->userRepository->save($user);

        $response = $this->post('/registro-horario/salida', [
            'userUuid' => $savedUser->uuid()->getValue()
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('error', 'No existe un registro de entrada abierto para cerrar.');
    }

    public function test_registro_horario_validation(): void
    {
        // Test UUID requerido para entrada
        $response = $this->post('/registro-horario/entrada', []);
        $response->assertSessionHasErrors(['userUuid']);

        // Test UUID inválido para entrada
        $response = $this->post('/registro-horario/entrada', [
            'userUuid' => 'invalid-uuid'
        ]);
        $response->assertSessionHasErrors(['userUuid']);

        // Test UUID requerido para salida
        $response = $this->post('/registro-horario/salida', []);
        $response->assertSessionHasErrors(['userUuid']);

        // Test UUID inválido para salida
        $response = $this->post('/registro-horario/salida', [
            'userUuid' => 'invalid-uuid'
        ]);
        $response->assertSessionHasErrors(['userUuid']);
    }

    public function test_registro_horario_with_selected_user(): void
    {
        // Crear un usuario
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $this->userRepository->save($user); // Save first to get ID

        // Load the user, fichar entrada y salida varias veces para el mismo día
        $userWithTimeEntries = $this->userRepository->findById(new UserId($savedUser->id()->getValue()));
        $userWithTimeEntries->ficharEntrada(); // Open
        $this->userRepository->save($userWithTimeEntries);
        sleep(1); // Ensure distinct timestamps
        $userWithTimeEntries->ficharSalida(); // Close
        $this->userRepository->save($userWithTimeEntries);

        sleep(1); // Ensure distinct timestamps
        $userWithTimeEntries->ficharEntrada(); // Open
        $this->userRepository->save($userWithTimeEntries);
        sleep(1); // Ensure distinct timestamps
        $userWithTimeEntries->ficharSalida(); // Close
        $this->userRepository->save($userWithTimeEntries); // Save final state

        // Re-fetch user to ensure aggregate is correctly loaded
        $fetchedUser = $this->userRepository->findById(new UserId($savedUser->id()->getValue()));
        $this->assertCount(2, $fetchedUser->registrosHorarios());
        $this->assertFalse($fetchedUser->registrosHorarios()[0]->isAbierto());
        $this->assertFalse($fetchedUser->registrosHorarios()[1]->isAbierto());

        $response = $this->get('/registro-horario?userUuid=' . $fetchedUser->uuid()->getValue());
        
        $response->assertStatus(200);
        $response->assertViewIs('registro_horario');
        
        // Verificar que se calcularon los segundos
        $viewData = $response->viewData('segundos');
        $this->assertGreaterThan(0, $viewData); // Should be > 0
    }

    public function test_registro_horario_shows_open_registro(): void
    {
        // Crear un usuario
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $this->userRepository->save($user); // Save first to get ID

        // Load user, fichar entrada
        $userWithOpenEntry = $this->userRepository->findById(new UserId($savedUser->id()->getValue()));
        $userWithOpenEntry->ficharEntrada(); // Fichar entrada
        $this->userRepository->save($userWithOpenEntry); // Guardar con registro abierto

        $response = $this->get('/registro-horario?userUuid=' . $savedUser->uuid()->getValue());
        
        $response->assertStatus(200);
        
        // Verificar que detecta registro abierto
        $tieneRegistroAbierto = $response->viewData('tieneRegistroAbierto');
        $this->assertTrue($tieneRegistroAbierto);
    }

    public function test_registro_horario_does_not_show_open_registro_if_all_closed(): void
    {
        // Crear un usuario
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $this->userRepository->save($user); // Save first to get ID

        // Load user, fichar entrada y salida
        $userWithClosedEntry = $this->userRepository->findById(new UserId($savedUser->id()->getValue()));
        $userWithClosedEntry->ficharEntrada(); // Fichar entrada
        $userWithClosedEntry->ficharSalida(); // Fichar salida
        $this->userRepository->save($userWithClosedEntry); // Guardar con registro cerrado

        $response = $this->get('/registro-horario?userUuid=' . $savedUser->uuid()->getValue());
        
        $response->assertStatus(200);
        
        // Verificar que no detecta registro abierto
        $tieneRegistroAbierto = $response->viewData('tieneRegistroAbierto');
        $this->assertFalse($tieneRegistroAbierto);
    }
}
