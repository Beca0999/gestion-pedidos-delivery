<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rider>
 */
class RiderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->rider(),
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('##########'),
            'vehicle_type' => fake()->randomElement(['Bicicleta', 'Motocicleta']),
            'is_available' => true,
        ];
    }
}
