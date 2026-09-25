<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('en_PH');

        return [
            'first_name' => $faker->firstName(),
            'middle_name' => $faker->optional(0.7)->lastName(), // Filipino middle name = mother's maiden surname
            'last_name' => $faker->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => '09'.fake()->numerify('#########'),
            'address' => $faker->optional(0.85)->address(),
            'valid_id_type' => fake()->randomElement([
                'Driver\'s License',
                'Passport',
                'UMID',
                'PhilSys ID',
                'Voter\'s ID',
                'SSS ID',
                'PhilHealth ID',
            ]),
            'valid_id_number' => strtoupper(fake()->bothify('??-####-#####')),
        ];
    }
}
