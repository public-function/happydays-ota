<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoomTypesSeeder::class,
            RatePlansSeeder::class,
            HotelsSeeder::class,
            ProductOffersSeeder::class,
            InventorySeeder::class,
            SampleBookingsSeeder::class,
        ]);
    }
}
