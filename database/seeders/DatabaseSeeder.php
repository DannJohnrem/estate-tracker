<?php

namespace Database\Seeders;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(
            [
                ClientSeeder::class,
                LotSeeder::class,
                PaymentSeeder::class,
                RolePermissionSeeder::class,
            ]
        );
    }
}
