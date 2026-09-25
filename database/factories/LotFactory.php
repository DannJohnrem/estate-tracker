<?php

namespace Database\Factories;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalContractPrice = fake()->numberBetween(500_000, 3_000_000);
        $downPayment = round($totalContractPrice * fake()->randomFloat(2, 0.1, 0.2), 2);
        $termMonths = fake()->randomElement([24, 36, 48, 60]);
        $monthlyAmortization = round(($totalContractPrice - $downPayment) / $termMonths, 2);
        $startDate = fake()->dateTimeBetween('-3 years', '-1 month');

        return [
            'client_id' => Client::factory(),
            'lot_number' => fake()->numberBetween(1, 40),
            'block_number' => fake()->numberBetween(1, 15),
            'subdivision' => fake()->randomElement(['Greenfield Estates', 'Sunrise Village', 'Palm Grove Residences', 'Emerald Hills']),
            'phase' => 'Phase '.fake()->numberBetween(1, 4),
            'lot_area' => fake()->randomFloat(2, 60, 200),
            'total_contract_price' => $totalContractPrice,
            'down_payment' => $downPayment,
            'monthly_amortization' => $monthlyAmortization,
            'term_months' => $termMonths,
            'months_paid' => 0,
            'start_date' => $startDate,
            'next_due_date' => Carbon::parse($startDate)->addMonth(),
            'status' => 'active',
        ];
    }

    /**
     * Active lot, partway through its term.
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            $monthsPaid = fake()->numberBetween(0, $attributes['term_months'] - 1);

            return [
                'status' => 'active',
                'months_paid' => $monthsPaid,
                'next_due_date' => Carbon::parse($attributes['start_date'])->addMonths($monthsPaid + 1),
            ];
        });
    }

    /**
     * Delinquent lot — overdue next_due_date guaranteed to be in the past.
     */
    public function delinquent(): static
    {
        return $this->state(function (array $attributes) {
            $monthsPaid = fake()->numberBetween(0, (int) ($attributes['term_months'] * 0.4));

            return [
                'status' => 'delinquent',
                'months_paid' => $monthsPaid,
                'next_due_date' => now()->subDays(fake()->numberBetween(5, 60)),
            ];
        });
    }

    /**
     * Fully paid lot — no outstanding balance.
     */
    public function fullyPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'fully_paid',
            'months_paid' => $attributes['term_months'],
            'next_due_date' => null,
        ]);
    }
}
