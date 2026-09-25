<?php

namespace Database\Seeders;

use App\Models\Lot;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Lot::all()->each(function (Lot $lot) {
            if ($lot->months_paid < 1) {
                return;
            }

            for ($i = 0; $i < $lot->months_paid; $i++) {
                Payment::factory()
                    ->for($lot)
                    ->create([
                        'amount' => $lot->monthly_amortization,
                        'paid_at' => Carbon::parse($lot->start_date)->addMonths($i + 1),
                    ]);
            }
        });
    }
}
