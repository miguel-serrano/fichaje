<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $entrada = fake()->dateTimeBetween('-3 months', '-1 hour')->getTimestamp();
        $salida = $entrada + fake()->numberBetween(4 * 3600, 9 * 3600);
        $now = time();

        return [
            'user_id' => User::factory(),
            'entrada' => $entrada,
            'salida' => $salida,
            'auto_closed' => false,
            'auto_close_reason' => null,
            'created_at' => $entrada,
            'updated_at' => min($salida, $now),
        ];
    }

    /**
     * Entry still open (no salida).
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'salida' => null,
            'entrada' => time() - fake()->numberBetween(60, 4 * 3600),
        ]);
    }

    /**
     * Auto-closed entry.
     */
    public function autoClosed(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_closed' => true,
            'auto_close_reason' => 'Cierre automático por fin de jornada',
        ]);
    }
}
