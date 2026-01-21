<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\HolidayRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HolidayRequest>
 */
class HolidayRequestFactory extends Factory
{
    protected $model = HolidayRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 day', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+2 months');
        $now = time();

        return [
            'user_id' => User::factory(),
            'start_date' => strtotime($startDate->format('Y-m-d').' 00:00:00'),
            'end_date' => strtotime($endDate->format('Y-m-d').' 00:00:00'),
            'status' => 'pending',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}
