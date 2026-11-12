<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'customer_name' => fake()->name(),
            'phone' => fake()->numerify('##########'),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'total' => fake()->randomFloat(2, 100, 800),
            'status' => 'Entregado',
            'payment_method' => 'Efectivo',
            'payment_status' => 'Pendiente',
        ];
    }
}
