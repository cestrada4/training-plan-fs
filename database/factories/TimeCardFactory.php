<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\TimeCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeCard>
 */
class TimeCardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'date' => fake()->dateTimeBetween('-3 months', 'yesterday')->format('Y-m-d'),
            'total_hours' => fake()->numberBetween(16, 40) / 4,
        ];
    }
}
