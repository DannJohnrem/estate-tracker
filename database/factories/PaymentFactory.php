<?php

namespace Database\Factories;

use App\Models\Lot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lot_id' => Lot::factory(),
            'amount' => fake()->randomFloat(2, 3000, 25000),
            'paid_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'method' => fake()->randomElement(['cash', 'bank_transfer', 'check', 'gcash']),
            'notes' => fake()->optional(0.3)->sentence(),
            'recorded_by' => User::inRandomOrder()->value('id'),
        ];
    }
}
