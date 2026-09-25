<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Lot;
use Illuminate\Database\Seeder;

class LotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::all()->each(function (Client $client) {
            $lotCount = fake()->numberBetween(1, 2);

            for ($i = 0; $i < $lotCount; $i++) {
                // Weighted: mostly active, some delinquent, some fully paid
                $state = fake()->randomElement(['active', 'active', 'active', 'delinquent', 'fullyPaid']);

                Lot::factory()
                    ->for($client)
                    ->{$state}()
                    ->create();
            }
        });
    }
}
