<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $now = time();

        return [
            'uuid' => (string) Str::orderedUuid(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * Indicate that the user should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the user should be an admin.
     * Note: After creation, use $user->assignRole('super_admin') or $user->assignRole('admin')
     * to grant admin permissions through the role system.
     */
    public function admin(): static
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            $superAdminRole = \App\Models\Role::where('slug', 'super_admin')->first();
            if ($superAdminRole) {
                $user->roles()->syncWithoutDetaching([$superAdminRole->id]);
            }
        });
    }
}
