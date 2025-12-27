<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegistroHorarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_fichar_entrada_y_salida()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Fichar entrada
        $response = $this->post(route('registro_horario.fichar'));
        $response->assertRedirect(route('registro_horario.index'));
        $this->assertDatabaseHas('registro_horarios', [
            'user_id' => $user->id,
            'salida' => null,
        ]);

        // Fichar salida
        $response = $this->post(route('registro_horario.fichar'));
        $response->assertRedirect(route('registro_horario.index'));
        $this->assertDatabaseMissing('registro_horarios', [
            'user_id' => $user->id,
            'salida' => null,
        ]);
    }

    public function test_sumatorio_segundos()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('registro_horario.fichar'));
        sleep(2);
        $this->post(route('registro_horario.fichar'));
        $response = $this->get(route('registro_horario.index'));

        $response->assertSee('Tiempo acumulado hoy:');
    }
}

