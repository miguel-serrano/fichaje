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

        return [
            'user_id' => User::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'status' => 'pending',
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
